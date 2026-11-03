<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FBA_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_ajax_fba_save_settings', array( __CLASS__, 'ajax_save' ) );
	}

	public static function add_page() {
		add_options_page(
			__( 'Flatsome Motion', 'flatsome-better-animations' ),
			__( 'Flatsome Motion', 'flatsome-better-animations' ),
			'manage_options',
			'flatsome-better-animations',
			array( __CLASS__, 'render' )
		);
	}

	public static function enqueue( $hook ) {
		if ( $hook !== 'settings_page_flatsome-better-animations' ) {
			return;
		}

		wp_enqueue_style(
			'fba-admin',
			FBA_URL . 'assets/css/admin.css',
			array(),
			FBA_VERSION
		);

		wp_enqueue_script(
			'fba-admin',
			FBA_URL . 'assets/js/admin.js',
			array(),
			FBA_VERSION,
			true
		);

		wp_localize_script(
			'fba-admin',
			'fbaAdmin',
			array(
				'settings' => FBA_Plugin::get_options(),
				'library'  => FBA_Library::frontend_library(),
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'fba_save_settings' ),
			)
		);
	}

	public static function ajax_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Ingen tilgang.', 'flatsome-better-animations' ) ), 403 );
		}

		check_ajax_referer( 'fba_save_settings', 'nonce' );

		$raw = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : '';
		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$raw     = is_array( $decoded ) ? $decoded : array();
		}
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		$clean = FBA_Plugin::sanitize_settings( $raw );
		update_option( 'fba_settings', $clean );

		wp_send_json_success(
			array(
				'settings' => FBA_Plugin::get_options(),
				'message'  => __( 'Endringene er lagret', 'flatsome-better-animations' ),
			)
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap fba-admin">
			<div class="fba-app">
				<header class="fba-top">
					<div>
						<h1><?php esc_html_e( 'Flatsome Motion', 'flatsome-better-animations' ); ?></h1>
						<p><?php esc_html_e( 'Inngangsanimasjoner for hele nettstedet, med live forhåndsvisning.', 'flatsome-better-animations' ); ?></p>
					</div>
					<span class="fba-pill">v<?php echo esc_html( FBA_VERSION ); ?></span>
				</header>

				<nav class="fba-nav" role="tablist" aria-label="<?php esc_attr_e( 'Innstillinger', 'flatsome-better-animations' ); ?>" id="fba-nav"></nav>

				<main class="fba-main">
					<section class="fba-tab is-on" id="t-anim" role="tabpanel">
						<div class="fba-card">
							<h2><?php esc_html_e( 'Standardanimasjon', 'flatsome-better-animations' ); ?></h2>
							<p class="fba-sub"><?php echo wp_kses_post( __( 'Velg hva <code>.reveal</code> gjør når ingenting annet er angitt. I UX Builder: stagger på <strong>raden</strong>, animasjon på kolonne/tekst. Hold musen over en animasjon for å se den.', 'flatsome-better-animations' ) ); ?></p>
							<div class="fba-grid" id="fba-lib"></div>
						</div>
						<div class="fba-card">
							<h2><?php esc_html_e( 'Utløser', 'flatsome-better-animations' ); ?></h2>
							<p class="fba-sub"><?php esc_html_e( 'Når og hvor ofte animasjonen spilles av.', 'flatsome-better-animations' ); ?></p>
							<div id="fba-trig"></div>
						</div>
					</section>

					<section class="fba-tab" id="t-text" role="tabpanel" hidden>
						<div class="fba-card">
							<h2><?php esc_html_e( 'Tekstanimasjon', 'flatsome-better-animations' ); ?></h2>
							<p class="fba-sub"><?php echo wp_kses_post( __( 'Standard for overskrifter og tekst som avsløres bit for bit. I UX Builder: Motion → Tekst på tittel- og tekst-elementer.', 'flatsome-better-animations' ) ); ?></p>
							<div id="fba-textc"></div>
						</div>
					</section>

					<section class="fba-tab" id="t-time" role="tabpanel" hidden>
						<div class="fba-card">
							<h2><?php esc_html_e( 'Timing og bevegelse', 'flatsome-better-animations' ); ?></h2>
							<p class="fba-sub"><?php echo wp_kses_post( __( 'Gjelder alle <code>.reveal</code>-elementer. Forhåndsvisningen oppdateres mens du drar.', 'flatsome-better-animations' ) ); ?></p>
							<div id="fba-timec"></div>
						</div>
					</section>

					<section class="fba-tab" id="t-flat" role="tabpanel" hidden>
						<div class="fba-card">
							<h2><?php esc_html_e( 'Flatsome data-animate', 'flatsome-better-animations' ); ?></h2>
							<p class="fba-sub"><?php esc_html_e( 'Erstatter Flatsomes innebygde animasjoner med raskere og mykere varianter.', 'flatsome-better-animations' ); ?></p>
							<div class="fba-row is-toggle">
								<div>
									<label class="fba-t" for="c-override_flatsome"><?php esc_html_e( 'Overstyr innebygde animasjoner', 'flatsome-better-animations' ); ?></label>
									<span class="fba-h"><?php echo wp_kses_post( __( 'Gjør <code>[data-animate]</code>-elementer raskere og mer presise.', 'flatsome-better-animations' ) ); ?></span>
								</div>
								<label class="fba-switch">
									<input type="checkbox" id="c-override_flatsome" data-c="override_flatsome">
									<i></i>
								</label>
							</div>
							<div id="fba-flatc"></div>
						</div>
						<div class="fba-card">
							<h2><?php esc_html_e( 'Slik slår det ut', 'flatsome-better-animations' ); ?></h2>
							<p class="fba-sub"><?php esc_html_e( 'Din varighet mot Flatsomes standard.', 'flatsome-better-animations' ); ?></p>
							<div class="fba-cmp" id="fba-cmp"></div>
						</div>
					</section>

					<section class="fba-tab" id="t-a11y" role="tabpanel" hidden>
						<div class="fba-card">
							<h2><?php esc_html_e( 'Tilgjengelighet og ytelse', 'flatsome-better-animations' ); ?></h2>
							<div id="fba-a11y"></div>
						</div>
					</section>
				</main>

				<aside class="fba-preview" id="fba-preview">
					<div class="fba-pv">
						<header>
							<b><?php esc_html_e( 'Forhåndsvisning', 'flatsome-better-animations' ); ?></b>
							<button type="button" class="fba-btn" id="fba-replay"><?php esc_html_e( 'Spill av', 'flatsome-better-animations' ); ?></button>
						</header>
						<div class="fba-canvas">
							<h3 id="fba-pt"></h3>
							<p id="fba-pp"><?php esc_html_e( 'Bygg sider som føles lette. Elementene glir inn når leseren når dem.', 'flatsome-better-animations' ); ?></p>
							<div class="fba-blk" id="fba-pb">
								<b><?php esc_html_e( 'Produktkort', 'flatsome-better-animations' ); ?></b>
								<span><?php esc_html_e( 'Dette blokk-elementet bruker valgt animasjon.', 'flatsome-better-animations' ); ?></span>
							</div>
						</div>
						<div class="fba-meta">
							<span id="fba-m1"></span>
							<span id="fba-m2"></span>
						</div>
					</div>
				</aside>
			</div>

			<div class="fba-save" id="fba-save" role="region" aria-label="<?php esc_attr_e( 'Ulagrede endringer', 'flatsome-better-animations' ); ?>">
				<span><?php esc_html_e( 'Du har ulagrede endringer', 'flatsome-better-animations' ); ?></span>
				<button type="button" class="fba-btn" id="fba-rst"><?php esc_html_e( 'Tilbakestill', 'flatsome-better-animations' ); ?></button>
				<button type="button" class="fba-btn is-primary" id="fba-sv"><?php esc_html_e( 'Lagre endringer', 'flatsome-better-animations' ); ?></button>
			</div>
			<div class="fba-toast" id="fba-toast" role="status"><?php esc_html_e( 'Endringene er lagret', 'flatsome-better-animations' ); ?></div>
		</div>
		<?php
	}
}
