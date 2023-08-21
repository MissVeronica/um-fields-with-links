<?php
/**
 * Plugin Name:     Ultimate Member - Fields With Links
 * Description:     Extension to Ultimate Member to include a Link in the Profile Form's Field Value and/or Field Label.
 * Version:         1.0.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.6.10
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
if ( ! class_exists( 'UM' ) ) return;

class UM_Field_With_Links {

    public $links = array( 'value' => false, 'label' => false, 'icon' => false );

    function __construct() {

        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {

            add_filter( 'um_settings_structure', array( $this, 'create_setting_structures' ), 10, 1 );
        }

        add_filter( 'um_profile_field_filter_hook__', array( $this, 'um_field_value_with_link' ), 200, 3 );
        add_filter( 'um_get_form_fields',             array( $this, 'um_field_label_with_link' ), 200, 1 );
    }

    public function um_field_value_with_link( $value, $data, $type ) {

        if ( UM()->fields()->set_mode == 'profile' ) {

            if ( isset( UM()->fields()->viewing ) && UM()->fields()->viewing === true && ! empty( $value) ) {

                $this->get_field_meta_key_with_link( 'value' );

                if ( is_array( $this->links['value'] ) && in_array( $data['metakey'], array_keys( $this->links['value'] ))) {

                    $url = str_replace( array( '{userid}', '{value}' ), array( um_profile_id(), $value ), $this->links['value'][$data['metakey']]['url'] );

                    $onclick_alert = $this->alert_external_url_link( $url );

                    $value = '<a href="' . $url . '" target="_blank" class="real_url field_value_with_link" title="' . esc_attr( $this->links['value'][$data['metakey']]['title'] ) . '" ' . $onclick_alert . '>' . $value . '</a>';

                    if ( ! empty( $this->links['value'][$data['metakey']]['icon'] ) ) {
                        $value .= ' <i class="' . esc_attr( $this->links['value'][$data['metakey']]['icon'] ) . '"></i>';
                    }
                }
            }
        }
        return $value;
    }

    public function um_field_label_with_link( $array ) {

        if ( UM()->fields()->set_mode == 'profile' ) {

            if ( isset( UM()->fields()->viewing ) && UM()->fields()->viewing === true ) {

                $this->get_field_meta_key_with_link( 'label' );

                if ( is_array( $this->links['label'] )) {
                    foreach( $this->links['label'] as $key => $data ) {

                        if ( isset( $array[$key] )) {

                            $url = str_replace( '{userid}', um_profile_id(),  $this->links['label'][$array[$key]['metakey']]['url'] );
                            $onclick_alert = $this->alert_external_url_link( $url );

                            $array[$key]['label'] = str_replace( '{link}', '<a href="' . $url . '" target="_blank" class="real_url field_label_with_link" title="' . esc_attr( $this->links['label'][$array[$key]['metakey']]['title'] ) . '" ' . $onclick_alert . '>', $array[$key]['label'] );
                            $array[$key]['label'] = str_replace( '{/link}', '</a>', $array[$key]['label'] );

                            if ( strpos( $array[$key]['label'], '</a>' ) === false ) {
                                $array[$key]['label'] .= '</a>';
                            }

                            if ( ! empty( $this->links['label'][$array[$key]['metakey']]['icon'] )) {
                                $array[$key]['label'] .= ' <i class="' . esc_attr( $this->links['label'][$array[$key]['metakey']]['icon'] ) . '"></i>';
                            }
                        }
                    }
                }
            }
        }
        return $array;
    }

    public function alert_external_url_link( $url ) {

        $onclick_alert = '';

        if ( UM()->options()->get( 'allow_url_redirect_confirm' ) && $url !== wp_validate_redirect( $url ) ) {
            $onclick_alert = sprintf(
                ' onclick="' . esc_attr( 'return confirm( "%s" );' ) . '"',
                // translators: %s: link.
                esc_js( sprintf( __( 'This link leads to a 3rd-party website. Make sure the link is safe and you really want to go to this website: \'%s\'', 'ultimate-member' ), $url ) )
            );
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

    public function create_setting_structures( $settings_structure ) {

        $settings_structure['appearance']['sections']['']['fields'][] = array(
            'id'            => 'um_field_meta_key_label_with_link',
            'type'          => 'textarea',
            'label'         => __( 'Field Label With Link - meta_key, url, title, icon (one per line)', 'ultimate-member' ),
            'tooltip'       => __( 'Enter the meta_key comma separated with the url, title and UM icon. Placeholder in the url: {userid}, UM Forms Builder label placeholder: {link], {/link}', 'ultimate-member' ),
        );

        $settings_structure['appearance']['sections']['']['fields'][] = array(
            'id'            => 'um_field_meta_key_value_with_link',
            'type'          => 'textarea',
            'label'         => __( 'Field Value With Link - meta_key, url, title, icon (one per line)', 'ultimate-member' ),
            'tooltip'       => __( 'Enter the meta_key comma separated with the url, title and UM icon. Placeholders in the url: {userid}, {value}', 'ultimate-member' ),
        );

        return $settings_structure;
    }
}

new UM_Field_With_Links();
