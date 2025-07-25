<?php

namespace DMG\RML;

/**
 * Handles the internationalisation setup for the plugin.
 */
class I18n {




    /**
     * The plugin's text domain.
     * @var string
     */
    private $text_domain = 'dmg-rml';




    /**
     * Adds the action to load the text domain.
     */
    public function __construct() {

        add_action( 'plugins_loaded', array($this, 'load_textdomain') );

    }




    /**
     * Loads the plugin text domain for translation.
     */
    public function load_textdomain() {

        load_plugin_textdomain(
            $this->text_domain,
            false,
            dirname( plugin_basename( DMG_RML_PLUGIN_PATH ) ) . '/languages/'
        );

    }




}