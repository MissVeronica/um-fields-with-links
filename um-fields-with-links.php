<?php
/**
 * Plugin Name:     Ultimate Member - Fields With Links
 * Description:     Extension to Ultimate Member to include a Link in the Register and Profile Form's Field Value and/or Field Label.
 * Version:         2.6.5
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Plugin URI:      https://github.com/MissVeronica/um-fields-with-links
 * Update URI:      https://github.com/MissVeronica/um-fields-with-links
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.9.2
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'UM' ) ) return;

class UM_Field_With_Links {

    public $links = array( 'value' => false, 'label' => false, 'icon' => false );

    function __construct() {

        define( 'Plugin_Basename_FWL', plugin_basename( __FILE__ ));

        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {

            add_filter( 'um_settings_structure', array( $this, 'create_setting_structures' ), 10, 1 );
        }

        add_filter( 'um_ajax_get_members_data',       array( $this, 'um_ajax_get_members_data_with_link' ), 10, 3 );
        add_filter( 'um_profile_field_filter_hook__', array( $this, 'um_field_value_with_link' ), 200, 3 );

        add_filter( 'plugin_action_links_' . Plugin_Basename_FWL, array( $this, 'plugin_settings_link' ), 10, 1 );

        $this->get_field_meta_key_with_link( 'label' );
        if ( is_array( $this->links['label'] )) {
            foreach( $this->links['label'] as $key => $values ) {
                add_filter( "um_{$key}_form_show_field",  array( $this, 'um_field_label_with_link' ), 200, 2 );
            }
        }
    }

    public function plugin_settings_link( $links ) {

        $url = get_admin_url() . 'admin.php?page=um_options&tab=appearance';
        $links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings' ) . '</a>';

        return $links;
    }

    public function um_field_value_with_link( $value, $data, $type ) {

        if ( UM()->fields()->set_mode == 'profile' || defined( 'DOING_AJAX' )) {

            if ( ( isset( UM()->fields()->viewing ) && UM()->fields()->viewing === true ) || defined( 'DOING_AJAX' ) ) {

                if ( ! empty( $value)) {

                    $this->get_field_meta_key_with_link( 'value' );

                    if ( is_array( $this->links['value'] ) && in_array( $data['metakey'], array_keys( $this->links['value'] ))) {

                        if ( isset( $data['max_selections'] ) && $data['max_selections'] > 1 && isset( $data['options'] ) && is_array( $data['options'] )) {

                            $max = 0;
                            $data['options'] = array_map( 'trim', $data['options'] );
                            $array = array_map( 'trim', explode( ',', $value ));

                            foreach( $data['options'] as $selection ) {

                                if ( in_array( $selection, $array ) ) {
                                    $key = array_search( $selection, $array );

                                    $url = str_replace( array( '{userid}', '{value}' ), array( um_profile_id(), $this->make_url_value( $array[$key] )), $this->links['value'][$data['metakey']]['url'] );
                                    $onclick_alert = $this->alert_external_url_link( $url );
                                    $link = '<a href="' . $url . '" target="_blank" class="real_url field_value_with_link" title="' . esc_attr( $this->links['value'][$data['metakey']]['title'] ) . '" ' . $onclick_alert . '>' . $selection . '</a>';

                                    if ( ! empty( $this->links['value'][$data['metakey']]['icon'] ) ) {
                                        $link .= ' <i class="' . esc_attr( $this->links['value'][$data['metakey']]['icon'] ) . '"></i>';
                                    }

                                    $array[$key] = $link;

                                    $max++;
                                    if ( $max == $data['max_selections'] ) {
                                        break;
                                    }
                                }
                            }
                            $value = implode( ', ',  $array );

                        } else {

                            $url = str_replace( array( '{userid}', '{value}' ), array( um_profile_id(), $this->make_url_value( $value ) ), $this->links['value'][$data['metakey']]['url'] );
                            $onclick_alert = $this->alert_external_url_link( $url );
                            $value = '<a href="' . $url . '" target="_blank" class="real_url field_value_with_link" title="' . esc_attr( $this->links['value'][$data['metakey']]['title'] ) . '" ' . $onclick_alert . '>' . $value . '</a>';

                            if ( ! empty( $this->links['value'][$data['metakey']]['icon'] ) ) {
                                $value .= ' <i class="' . esc_attr( $this->links['value'][$data['metakey']]['icon'] ) . '"></i>';
                            }
                        }
                    }
                }
            }
        }

        return $value;
    }

    public function make_url_value( $value ) {

        $value = str_replace( array( '&', ' ' ), array( 'and', '-' ), $value );
        $value = strtolower( $value );
        global $um_html_view_function;
        $um_html_view_function->debug_cpu_update_profile( $value, __FUNCTION__, 'value', basename( $_SERVER['PHP_SELF'] ), __line__ );
        return $value;
    }

    public function um_ajax_get_members_data_with_link( $data_array, $user_id, $directory_data ) {

        if ( is_array( $this->links['label'] )) {
            foreach(  $this->links['label'] as $key => $value ) {

                if ( isset( $data_array['label_' . $key] )) {
                    $data_array['label_' . $key] = $this->reformat_label_link( $data_array['label_' . $key], $key );
                }
            }
        }

        return $data_array;
    }

    public function um_field_label_with_link( $output, $mode ) {

        if ( UM()->fields()->set_mode == 'profile' ) {

            if ( isset( UM()->fields()->viewing ) && UM()->fields()->viewing !== true ) {
                return $array;
            }

        } else {

            if ( UM()->fields()->set_mode != 'register' ) {
                return $array;
            }
        }

        $key = str_replace( array( 'um_', '_form_show_field' ), '', current_filter() );

        return $this->reformat_label_link( $output, $key );
    }

    public function reformat_label_link( $output, $key ) {

        if ( is_array( $this->links['label'] ) && isset( $this->links['label'][$key] )) {

            $url = str_replace( '{userid}', um_profile_id(),  $this->links['label'][$key]['url'] );
            $onclick_alert = $this->alert_external_url_link( $url );

            $field_icons = substr_count( $output, '</i>' );
            if ( ! empty( $this->links['value'][$key]['icon'] ) ) {
                $field_icons = $field_icons - 1;
            }

            $field_icon = false;
            if ( $field_icons == 1 ) {
                $split_output = explode( '</i>', $output );
                $output = $split_output[1];
                $field_icon = true;
            }

            $icon = '';
            if ( ! empty( $this->links['label'][$key]['icon'] )) {
                $icon = ' <i class="' . esc_attr( $this->links['label'][$key]['icon'] ) . '"></i>';
            }

            $output = str_replace( '{link}', '<a href="' . $url . '" target="_blank" class="real_url field_label_with_link" title="' .
                                                esc_attr( $this->links['label'][$key]['title'] ) . '" ' . $onclick_alert . '>', $output );

            if ( strpos( $output, '{/link}' )) {
                $output = str_replace( '{/link}', '</a>' . $icon, $output );

            } else {
                $output = str_replace( '</label>', '</a>' . $icon . '</label>', $output );
            }

            if ( $field_icon ) {
                $output = $split_output[0] . '</i>' . $output;
            }
        }

        return $output;
    }

    public function alert_external_url_link( $url ) {

        $onclick_alert = '';

        if ( UM()->fields()->set_mode == 'profile' || defined( 'DOING_AJAX' )) {

            if ( UM()->options()->get( 'allow_url_redirect_confirm' ) && $url !== wp_validate_redirect( $url ) ) {

                $onclick_alert = sprintf(
                    ' onclick="' . esc_attr( 'return confirm( "%s" );' ) . '"',
                    // translators: %s: link.
                    esc_js( sprintf( esc_html__( 'This link leads to a 3rd-party website. Make sure the link is safe and you really want to go to this website: \'%s\'', 'ultimate-member' ), $url ) )
                );
            }
        }

        return $onclick_alert;
    }

    public function get_field_meta_key_with_link( $type ) {

        if ( ! $this->links[$type] ) {

            $options = UM()->options()->get( "um_field_meta_key_{$type}_with_link" );

            if ( ! empty( $options )) {
                $options = array_map( 'sanitize_text_field', array_map( 'trim', explode( "\n", $options )));

                $this->links[$type] = array();
                foreach( $options as $option ) {
                    $items = array_map( 'trim', explode( ',', $option ));

                    if ( isset( $items[0] ) && ! empty( $items[0] )) {

                        if ( isset( $items[1] )) {
                            $this->links[$type][$items[0]]['url']   = $items[1];
                            $this->links[$type][$items[0]]['title'] = '';
                            $this->links[$type][$items[0]]['icon']  = '';
                        }
                        if ( isset( $items[2] )) {
                            $this->links[$type][$items[0]]['title'] = $items[2];
                        }
                        if ( isset( $items[3] )) {
                            $this->links[$type][$items[0]]['icon'] = $items[3];
                        }
                    }
                }
            }
        }
    }

    public function get_possible_plugin_update( $plugin ) {

        $plugin_data = get_plugin_data( __FILE__ );

        $documention = sprintf( ' <a href="%s" target="_blank" title="%s">%s</a>',
                                        esc_url( $plugin_data['PluginURI'] ),
                                        esc_html__( 'GitHub plugin documentation and download', 'ultimate-member' ),
                                        esc_html__( 'Documentation', 'ultimate-member' ));

        $description = sprintf( esc_html__( 'Plugin "Fields With Links" version %s - tested with UM 2.9.2 - %s', 'ultimate-member' ),
                                                                            $plugin_data['Version'], $documention );

        return $description;
    }

    public function create_setting_structures( $settings_structure ) {

        $prefix = '&nbsp; * &nbsp;';

        $settings_structure['appearance']['sections']['']['form_sections']['meta_key_label_with_link']['title'] = __( 'Fields With Links', 'ultimate-member' );
        $settings_structure['appearance']['sections']['']['form_sections']['meta_key_label_with_link']['description'] = $this->get_possible_plugin_update( 'um-fields-with-links' );

        $settings_structure['appearance']['sections']['']['form_sections']['meta_key_label_with_link']['fields'][] = array(
            'id'            => 'um_field_meta_key_label_with_link',
            'type'          => 'textarea',
            'label'         => $prefix . esc_html__( 'meta_key, url, title, icon (one set per line)', 'ultimate-member' ),
            'description'   => esc_html__( 'Enter the meta_key comma separated with the url, title and UM icon. Placeholder in the url: {userid}, UM Forms Builder label placeholder: {link], {/link}', 'ultimate-member' ),
        );

        $settings_structure['appearance']['sections']['']['form_sections']['meta_key_label_with_link']['fields'][] = array(
            'id'            => 'um_field_meta_key_value_with_link',
            'type'          => 'textarea',
            'label'         => $prefix . esc_html__( 'meta_key, url, title, icon (one set per line)', 'ultimate-member' ),
            'description'   => esc_html__( 'Enter the meta_key comma separated with the url, title and UM icon. Placeholders in the url: {userid}, {value}', 'ultimate-member' ),
        );

        return $settings_structure;
    }
}

new UM_Field_With_Links();


