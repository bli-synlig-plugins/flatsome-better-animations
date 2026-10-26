<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class FBA_Plugin {

    private static $instance = null;

    private static $defaults = array(
        'override_flatsome'   => true,
        'transform_duration'  => 550,
        'opacity_duration'    => 400,
        'waypoint_offset'     => 88,
        'stagger_step'        => 80,
        'reveal_system'       => true,
        'reveal_threshold'    => 12,
        'move_distance'       => 24,
    );

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts',  array( $this, 'enqueue_assets' ), 20 );
        add_action( 'wp_head',             array( $this, 'noscript_fallback' ), 1 );
        add_action( 'admin_menu',          array( $this, 'add_settings_page' ) );
        add_action( 'admin_init',          array( $this, 'register_settings' ) );
        add_action( 'plugin_action_links_' . plugin_basename( FBA_DIR . 'flatsome-better-animations.php' ),
                    array( $this, 'action_links' ) );
    }

    public static function get_options() {
        $saved = get_option( 'fba_settings', array() );
        return wp_parse_args( $saved, self::$defaults );
    }

    public function enqueue_assets() {
        $opts = self::get_options();

        wp_enqueue_style(
            'flatsome-better-animations',
            FBA_URL . 'assets/css/animations.css',
            array(),
            FBA_VERSION
        );

        wp_add_inline_style(
            'flatsome-better-animations',
            $this->build_dynamic_css( $opts )
        );

        wp_enqueue_script(
            'flatsome-better-animations',
            FBA_URL . 'assets/js/animations.js',
            array( 'jquery' ),
            FBA_VERSION,
            true   // footer
        );

        wp_localize_script(
            'flatsome-better-animations',
            'fbaSettings',
            array(
                'overrideFlatsome'  => (bool) $opts['override_flatsome'],
                'waypointOffset'    => (int)  $opts['waypoint_offset'],
                'staggerStep'       => (int)  $opts['stagger_step'],
                'revealSystem'      => (bool) $opts['reveal_system'],
                'revealThreshold'   => (int)  $opts['reveal_threshold'] / 100,
            )
        );
    }

    /**
     * Vis .reveal-elementer når JavaScript er deaktivert.
     * html.no-js i CSS dekker Flatsome; <noscript> virker uavhengig av tema.
     */
    public function noscript_fallback() {
        $opts = self::get_options();
        if ( empty( $opts['reveal_system'] ) && empty( $opts['override_flatsome'] ) ) {
            return;
        }
        ?>
        <noscript>
        <style id="fba-noscript-fallback">
            .reveal {
                opacity: 1 !important;
                transform: none !important;
                animation: none !important;
            }
            [data-animate]:not([data-animated="true"]) {
                opacity: 1 !important;
                transform: none !important;
                filter: none !important;
            }
        </style>
        </noscript>
        <?php
    }

    private function build_dynamic_css( $opts ) {
        $t  = (int) $opts['transform_duration'];
        $o  = (int) $opts['opacity_duration'];
        $d  = (int) $opts['move_distance'];
        $bt = (int) ( $t * 1.1 ); 
        $db = $d * 6;               

        return "
@media (prefers-reduced-motion: no-preference) {
    [data-animate] {
        transition:
            filter    {$t}ms cubic-bezier(0.22, 1, 0.36, 1),
            transform {$t}ms cubic-bezier(0.22, 1, 0.36, 1),
            opacity   {$o}ms ease-out !important;
    }
    [data-animate=bounceInDown],
    [data-animate=bounceInLeft],
    [data-animate=bounceInRight],
    [data-animate=bounceInUp] {
        transition:
            transform {$bt}ms cubic-bezier(0.34, 1.56, 0.64, 1),
            opacity   {$o}ms  ease-out !important;
    }

    /* Start-posisjoner — kun for uanimerte elementer */
    [data-animate=fadeInLeft]:not([data-animated=\"true\"])    { transform: translate3d(-{$d}px,  0,     0) !important; }
    [data-animate=fadeInRight]:not([data-animated=\"true\"])   { transform: translate3d( {$d}px,  0,     0) !important; }
    [data-animate=fadeInUp]:not([data-animated=\"true\"]),
    [data-animate=bounceInUp]:not([data-animated=\"true\"])    { transform: translate3d( 0,  {$d}px, 0) !important; }
    [data-animate=fadeInDown]:not([data-animated=\"true\"]),
    [data-animate=bounceInDown]:not([data-animated=\"true\"])  { transform: translate3d( 0, -{$d}px, 0) !important; }
    [data-animate=bounceInLeft]:not([data-animated=\"true\"])  { transform: translate3d(-{$db}px, 0, 0) !important; }
    [data-animate=bounceInRight]:not([data-animated=\"true\"]) { transform: translate3d( {$db}px, 0, 0) !important; }

    .reveal {
        --fba-move: {$d}px;
    }
}";
    }

    public function add_settings_page() {
        add_options_page(
            'Flatsome Better Animations',
            'Better Animations',
            'manage_options',
            'flatsome-better-animations',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'fba_settings_group',
            'fba_settings',
            array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
        );
    }

    public function sanitize_settings( $input ) {
        $clean = array();
        $clean['override_flatsome']  = ! empty( $input['override_flatsome'] );
        $clean['transform_duration'] = max( 100, min( 2000, (int) $input['transform_duration'] ) );
        $clean['opacity_duration']   = max( 100, min( 2000, (int) $input['opacity_duration'] ) );
        $clean['waypoint_offset']    = max( 50,  min( 100,  (int) $input['waypoint_offset'] ) );
        $clean['stagger_step']       = max( 0,   min( 500,  (int) $input['stagger_step'] ) );
        $clean['reveal_system']      = ! empty( $input['reveal_system'] );
        $clean['reveal_threshold']   = max( 0,   min( 100,  (int) $input['reveal_threshold'] ) );
        $clean['move_distance']      = max( 0,   min( 200,  (int) $input['move_distance'] ) );
        return $clean;
    }

    public function render_settings_page() {
        $opts = self::get_options();
        ?>
        <div class="wrap">
            <h1>Flatsome Better Animations</h1>
            <p class="description">Erstatter Flatsomes trege standard-animasjoner og legger til et eget <code>.reveal</code>-system.</p>

            <form method="post" action="options.php">
                <?php settings_fields( 'fba_settings_group' ); ?>

                <h2 style="margin-top:2em; border-bottom:1px solid #ddd; padding-bottom:.5em;">
                    Flatsome data-animate override
                </h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Aktiver override</th>
                        <td>
                            <label>
                                <input type="checkbox" name="fba_settings[override_flatsome]" value="1"
                                    <?php checked( $opts['override_flatsome'] ); ?>>
                                Overstyr Flatsomes innebygde animasjoner
                            </label>
                            <p class="description">Gjør <code>[data-animate]</code>-elementer raskere og snappere.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Transform-varighet (ms)</th>
                        <td>
                            <input type="number" name="fba_settings[transform_duration]"
                                value="<?php echo esc_attr( $opts['transform_duration'] ); ?>"
                                min="100" max="2000" step="50" class="small-text">
                            <p class="description">Flatsome standard: <strong>1600ms</strong>. Anbefalt: 400–600ms.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Opacity-varighet (ms)</th>
                        <td>
                            <input type="number" name="fba_settings[opacity_duration]"
                                value="<?php echo esc_attr( $opts['opacity_duration'] ); ?>"
                                min="100" max="2000" step="50" class="small-text">
                            <p class="description">Flatsome standard: <strong>700ms</strong>. Anbefalt: 300–450ms.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Forflytningsdistanse (px)</th>
                        <td>
                            <input type="number" name="fba_settings[move_distance]"
                                value="<?php echo esc_attr( $opts['move_distance'] ); ?>"
                                min="0" max="200" step="4" class="small-text">
                            <p class="description">Flatsome standard: <strong>70px</strong>. Anbefalt: 20–30px.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Waypoint-offset (%)</th>
                        <td>
                            <input type="number" name="fba_settings[waypoint_offset]"
                                value="<?php echo esc_attr( $opts['waypoint_offset'] ); ?>"
                                min="50" max="100" step="1" class="small-text">
                            <p class="description">Flatsome standard: <strong>101%</strong> (utenfor skjermen!). Anbefalt: 85–90%.</p>
                        </td>
                    </tr>
                </table>

                <h2 style="margin-top:2em; border-bottom:1px solid #ddd; padding-bottom:.5em;">
                    .reveal scroll-system
                </h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Aktiver .reveal-system</th>
                        <td>
                            <label>
                                <input type="checkbox" name="fba_settings[reveal_system]" value="1"
                                    <?php checked( $opts['reveal_system'] ); ?>>
                                Last CSS og JS for <code>.reveal</code>-klassen
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Stagger-steg (ms)</th>
                        <td>
                            <input type="number" name="fba_settings[stagger_step]"
                                value="<?php echo esc_attr( $opts['stagger_step'] ); ?>"
                                min="0" max="500" step="10" class="small-text">
                            <p class="description">Forsinkelse mellom hvert barn i <code>.reveal-stagger</code>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Synlighetsterskel (%)</th>
                        <td>
                            <input type="number" name="fba_settings[reveal_threshold]"
                                value="<?php echo esc_attr( $opts['reveal_threshold'] ); ?>"
                                min="0" max="100" step="1" class="small-text">
                            <p class="description">Hvor mye av elementet (%) som må være synlig før det trigges.</p>
                        </td>
                    </tr>
                </table>

                <h2 style="margin-top:2em; border-bottom:1px solid #ddd; padding-bottom:.5em;">
                    Bruksdokumentasjon
                </h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Tilgjengelige klasser</th>
                        <td>
                            <code>.reveal</code> — fade + opp (standard)<br>
                            <code>.reveal.reveal--down</code> — fade + ned<br>
                            <code>.reveal.reveal--left</code> — fade + fra venstre<br>
                            <code>.reveal.reveal--right</code> — fade + fra høyre<br>
                            <code>.reveal.reveal--fade</code> — kun fade, ingen forflytning<br>
                            <code>.reveal.reveal--zoom</code> — zoom inn<br>
                            <code>.reveal.reveal--zoom-out</code> — zoom ut<br>
                            <code>.reveal.reveal--tilt</code> — 3D-tilt perspektiv<br><br>
                            <strong>Stagger:</strong> pakk elementer i <code>.reveal-stagger</code><br>
                            <strong>Egendefinert delay:</strong> <code>data-reveal-delay="300"</code> (ms)<br><br>
                            Uten JavaScript vises elementene umiddelbart (ingen animasjon).
                        </td>
                    </tr>
                </table>

                <?php submit_button( 'Lagre innstillinger' ); ?>
            </form>
        </div>
        <?php
    }

    public function action_links( $links ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=flatsome-better-animations' ) . '">Innstillinger</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
}
