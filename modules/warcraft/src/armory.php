<?php
/**
 * ============================================================
 * Slayer Alliance Master Suite — Character Armory Module
 * ============================================================
 * @file        modules/armory.php
 * @version     2.0.0
 * @since       1.0.0
 * @author      DieOuwe <www.dieouwe.nl>
 * @copyright   2025 Slayer Alliance · discord.gg/y8Pu5qsEbQ
 * @license     GPL-2.0-or-later
 *
 * Shortcode:   [sa_armory]
 * Admin tab:   sa_status_armory()
 *
 * API gebruikt:
 *   GET /profile/wow/character/{realm}/{name}                    → summary
 *   GET /profile/wow/character/{realm}/{name}/character-media    → renders/avatar
 *   GET /profile/wow/character/{realm}/{name}/equipment          → gear + item levels
 *   GET /profile/wow/character/{realm}/{name}/achievements       → achievement points
 *   GET /profile/wow/character/{realm}/{name}/statistics         → kills, deaths, etc.
 *
 * Caching: WP Transients — 1 uur per karakter
 * URL param: ?char=NaamHier&realm=sporeggar   (of via shortcode attr)
 * ============================================================
 * Last updated: 2025-06-06
 * Status: Productie
 * ============================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Constanten ─────────────────────────────────────────────
define( 'SA_ARMORY_VERSION',    '2.0.0' );
define( 'SA_ARMORY_CACHE_TTL',  HOUR_IN_SECONDS );
define( 'SA_ARMORY_CACHE_PFX',  'sa_armory_' );

// ─── WoW inventory slot namen ─────────────────────────────────
$SA_SLOT_LABELS = [
    'HEAD'        => '🪖 Head',     'NECK'      => '📿 Neck',
    'SHOULDER'    => '🛡 Shoulder', 'BACK'      => '🧣 Back',
    'CHEST'       => '🥼 Chest',    'SHIRT'     => '👔 Shirt',
    'TABARD'      => '🏴 Tabard',   'WRIST'     => '⌚ Wrist',
    'HANDS'       => '🧤 Gloves',   'WAIST'     => '🪢 Belt',
    'LEGS'        => '👖 Legs',     'FEET'      => '👟 Boots',
    'FINGER_1'    => '💍 Ring 1',   'FINGER_2'  => '💍 Ring 2',
    'TRINKET_1'   => '🔮 Trinket 1','TRINKET_2' => '🔮 Trinket 2',
    'MAIN_HAND'   => '⚔ Main Hand', 'OFF_HAND'  => '🛡 Off Hand',
];

// ─── Class-kleur mapping (gedeeld met roster.php) ────────────
$SA_ARMORY_CLASS_COLORS = [
    'Death Knight' => '#C41E3A', 'Demon Hunter' => '#A330C9',
    'Druid'        => '#FF7C0A', 'Evoker'       => '#33937F',
    'Hunter'       => '#AAD372', 'Mage'         => '#3FC7EB',
    'Monk'         => '#00FF98', 'Paladin'      => '#F48CBA',
    'Priest'       => '#FFFFFF', 'Rogue'        => '#FFF468',
    'Shaman'       => '#0070DD', 'Warlock'      => '#8788EE',
    'Warrior'      => '#C69B3A', 'default'      => '#a11692',
];

// ═══════════════════════════════════════════════════════════════
// SECTIE 1 — DATA OPHALEN
// ═══════════════════════════════════════════════════════════════

/**
 * Haal alle armory data op voor één karakter.
 * Parallel-achtige aanpak: 4 API calls, resultaten gecombineerd.
 *
 * @param string $realm Realm slug
 * @param string $name  Character naam
 * @return array|WP_Error
 */
function sa_armory_fetch_character( string $realm, string $name ): array|WP_Error {
    $realm = sanitize_title( strtolower( $realm ) );
    $name  = sanitize_title( strtolower( $name ) );

    if ( empty( $realm ) || empty( $name ) ) {
        return new WP_Error( 'invalid_input', 'Realm of naam is leeg.' );
    }

    $cache_key = SA_ARMORY_CACHE_PFX . md5( $realm . '_' . $name );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) return $cached;

    $token = sa_get_valid_token();
    if ( is_wp_error( $token ) || empty( $token ) ) {
        return new WP_Error( 'token_fail', 'Geen geldig Blizzard token.' );
    }

    $base    = "https://eu.api.blizzard.com/profile/wow/character/{$realm}/{$name}";
    $headers = [ 'Authorization' => 'Bearer ' . $token ];
    $locale  = 'locale=en_GB&namespace=profile-eu';

    // ── 4 API calls ───────────────────────────────────────────
    $endpoints = [
        'summary'      => "{$base}?{$locale}",
        'media'        => "{$base}/character-media?{$locale}",
        'equipment'    => "{$base}/equipment?{$locale}",
        'achievements' => "{$base}/achievements/statistics?{$locale}",
    ];

    $results = [];
    foreach ( $endpoints as $key => $url ) {
        $r = wp_remote_get( $url, [ 'timeout' => 12, 'headers' => $headers ] );
        if ( is_wp_error( $r ) ) {
            $results[ $key ] = null;
            continue;
        }
        $code = (int) wp_remote_retrieve_response_code( $r );
        $results[ $key ] = ( 200 === $code )
            ? json_decode( wp_remote_retrieve_body( $r ), true )
            : null;
    }

    if ( empty( $results['summary'] ) ) {
        return new WP_Error( 'char_not_found', "Karakter '{$name}' op realm '{$realm}' niet gevonden (HTTP 404 of leeg)." );
    }

    // ── Media: avatar + render ────────────────────────────────
    $avatar  = '';
    $render  = '';
    foreach ( $results['media']['assets'] ?? [] as $asset ) {
        match ( $asset['key'] ?? '' ) {
            'avatar'   => $avatar = esc_url_raw( $asset['value'] ?? '' ),
            'main-raw' => $render = esc_url_raw( $asset['value'] ?? '' ),
            default    => null,
        };
    }

    // ── Equipment → gear slots + gemiddeld ilvl ───────────────
    $equipped_items = [];
    $ilvl_sum       = 0;
    $ilvl_count     = 0;
    $ignore_slots   = [ 'SHIRT', 'TABARD' ];

    foreach ( $results['equipment']['equipped_items'] ?? [] as $item ) {
        $slot_type = $item['slot']['type'] ?? 'UNKNOWN';
        $ilvl      = (int) ( $item['level']['value'] ?? 0 );
        $quality   = $item['quality']['type'] ?? 'COMMON';

        $equipped_items[] = [
            'slot'    => $slot_type,
            'name'    => $item['item']['name'] ?? 'Onbekend item',
            'ilvl'    => $ilvl,
            'quality' => $quality,
            'icon'    => $item['media']['key']['href'] ?? '',
        ];

        if ( ! in_array( $slot_type, $ignore_slots, true ) && $ilvl > 0 ) {
            $ilvl_sum   += $ilvl;
            $ilvl_count += 1;
        }
    }

    $avg_ilvl = $ilvl_count > 0 ? round( $ilvl_sum / $ilvl_count, 1 ) : 0;

    // ── Achievement punten ────────────────────────────────────
    $achieve_pts = (int) ( $results['summary']['achievement_points'] ?? 0 );

    // ── Samengesteld resultaat ────────────────────────────────
    $data = [
        'name'              => $results['summary']['name'] ?? ucfirst( $name ),
        'realm'             => $results['summary']['realm']['name'] ?? ucfirst( $realm ),
        'realm_slug'        => $realm,
        'level'             => (int) ( $results['summary']['level'] ?? 0 ),
        'race'              => $results['summary']['race']['name'] ?? '',
        'class'             => $results['summary']['character_class']['name'] ?? '',
        'spec'              => $results['summary']['active_spec']['name'] ?? '',
        'faction'           => $results['summary']['faction']['name'] ?? '',
        'gender'            => $results['summary']['gender']['name'] ?? '',
        'item_level'        => (float) ( $results['summary']['equipped_item_level'] ?? $avg_ilvl ),
        'achievement_points'=> $achieve_pts,
        'guild'             => $results['summary']['guild']['name'] ?? '',
        'avatar'            => $avatar,
        'render'            => $render,
        'equipment'         => $equipped_items,
        'last_login'        => $results['summary']['last_login_timestamp'] ?? 0,
        'fetched'           => current_time( 'mysql' ),
    ];

    set_transient( $cache_key, $data, SA_ARMORY_CACHE_TTL );
    return $data;
}

// ═══════════════════════════════════════════════════════════════
// SECTIE 2 — HELPERS
// ═══════════════════════════════════════════════════════════════

/**
 * Vertaalt Blizzard quality type naar CSS klasse + kleur.
 */
function sa_armory_quality_class( string $quality ): string {
    return match( strtoupper( $quality ) ) {
        'POOR'      => 'q-poor',
        'COMMON'    => 'q-common',
        'UNCOMMON'  => 'q-uncommon',
        'RARE'      => 'q-rare',
        'EPIC'      => 'q-epic',
        'LEGENDARY' => 'q-legendary',
        'ARTIFACT'  => 'q-artifact',
        default     => 'q-common',
    };
}

// ═══════════════════════════════════════════════════════════════
// SECTIE 3 — AJAX cache flush
// ═══════════════════════════════════════════════════════════════

add_action( 'wp_ajax_sa_armory_flush', 'sa_armory_flush_handler' );
function sa_armory_flush_handler(): void {
    check_ajax_referer( 'sa_armory_flush_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Geen rechten', 403 );

    $name  = sanitize_title( $_POST['char'] ?? '' );
    $realm = sanitize_title( $_POST['realm'] ?? 'sporeggar' );

    if ( $name ) {
        delete_transient( SA_ARMORY_CACHE_PFX . md5( $realm . '_' . strtolower( $name ) ) );
        wp_send_json_success( "Cache gewist voor {$name} ({$realm})." );
    } else {
        // Flush alle armory transients
        global $wpdb;
        $like = $wpdb->esc_like( '_transient_' . SA_ARMORY_CACHE_PFX );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '{$like}%'" );
        wp_send_json_success( 'Alle armory caches gewist.' );
    }
}

// ═══════════════════════════════════════════════════════════════
// SECTIE 4 — SHORTCODE [sa_armory]
// ═══════════════════════════════════════════════════════════════

add_shortcode( 'sa_armory', 'sa_armory_shortcode' );

function sa_armory_shortcode( array $atts = [] ): string {
    global $SA_SLOT_LABELS, $SA_ARMORY_CLASS_COLORS;

    $atts = shortcode_atts( [
        'char'  => sanitize_text_field( $_GET['char'] ?? '' ),
        'realm' => sanitize_text_field( $_GET['realm'] ?? 'sporeggar' ),
    ], $atts, 'sa_armory' );

    $char  = sanitize_title( $atts['char'] );
    $realm = sanitize_title( $atts['realm'] );

    // ── CSS eenmalig laden ────────────────────────────────────
    if ( ! wp_style_is( 'sa-armory', 'enqueued' ) ) {
        wp_enqueue_style(
            'sa-armory',
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/armory.css',
            [],
            SA_ARMORY_VERSION
        );
    }

    // ── Zoekformulier als geen char opgegeven ─────────────────
    if ( empty( $char ) ) {
        ob_start(); ?>
        <div class="sa-armory-search-wrap">
            <h2>🛡 Slayer Alliance Armory</h2>
            <form method="get" action="" class="sa-armory-search-form">
                <input type="text" name="char"  placeholder="Karakternaam..." required
                       class="sa-armory-input">
                <input type="text" name="realm" value="sporeggar"
                       class="sa-armory-input" placeholder="Realm slug...">
                <button type="submit" class="sa-armory-btn">🔎 Zoeken</button>
            </form>
        </div>
        <?php return ob_get_clean();
    }

    // ── Data ophalen ──────────────────────────────────────────
    $data = sa_armory_fetch_character( $realm, $char );

    if ( is_wp_error( $data ) ) {
        return '<div class="sa-armory-error">⚠ ' . esc_html( $data->get_error_message() )
             . '<br><a href="' . esc_url( home_url( '/armory/' ) ) . '">← Terug</a></div>';
    }

    $class_color = $SA_ARMORY_CLASS_COLORS[ $data['class'] ] ?? $SA_ARMORY_CLASS_COLORS['default'];

    // ── Sorteer gear op slot volgorde ─────────────────────────
    $slot_order = array_keys( $SA_SLOT_LABELS );
    usort( $data['equipment'], function( $a, $b ) use ( $slot_order ) {
        $ai = array_search( $a['slot'], $slot_order, true );
        $bi = array_search( $b['slot'], $slot_order, true );
        return ( false === $ai ? 99 : $ai ) - ( false === $bi ? 99 : $bi );
    });

    // ── HTML ──────────────────────────────────────────────────
    ob_start();
    $render_url = $data['render'] ?: $data['avatar'];
    ?>
    <div class="sa-armory-wrap" style="--class-color:<?php echo esc_attr( $class_color ); ?>">

        <!-- ─── PROFIEL HERO ───────────────────────────────── -->
        <div class="sa-armory-hero">
            <?php if ( $render_url ) : ?>
            <div class="sa-armory-render-wrap">
                <img src="<?php echo esc_url( $render_url ); ?>"
                     alt="<?php echo esc_attr( $data['name'] ); ?> render"
                     class="sa-armory-render">
            </div>
            <?php endif; ?>

            <div class="sa-armory-profile">
                <h1 class="sa-armory-name">
                    <?php echo esc_html( $data['name'] ); ?>
                    <span class="sa-armory-level">Lv <?php echo (int) $data['level']; ?></span>
                </h1>

                <div class="sa-armory-class-line" style="color:<?php echo esc_attr( $class_color ); ?>">
                    <?php echo esc_html( $data['spec'] ); ?>
                    <?php echo esc_html( $data['class'] ); ?>
                </div>

                <div class="sa-armory-sub">
                    <?php echo esc_html( $data['race'] ); ?>
                    &middot; <?php echo esc_html( $data['faction'] ); ?>
                    &middot; <?php echo esc_html( $data['realm'] ); ?> EU
                </div>

                <?php if ( $data['guild'] ) : ?>
                <div class="sa-armory-guild">⚔ &lt;<?php echo esc_html( $data['guild'] ); ?>&gt;</div>
                <?php endif; ?>

                <div class="sa-armory-stats-row">
                    <div class="sa-armory-stat-box">
                        <span class="sa-stat-label">Item Level</span>
                        <span class="sa-stat-value"><?php echo number_format( $data['item_level'], 1 ); ?></span>
                    </div>
                    <div class="sa-armory-stat-box">
                        <span class="sa-stat-label">Achievement Pts</span>
                        <span class="sa-stat-value"><?php echo number_format( $data['achievement_points'] ); ?></span>
                    </div>
                    <?php if ( $data['last_login'] ) : ?>
                    <div class="sa-armory-stat-box">
                        <span class="sa-stat-label">Laatste login</span>
                        <span class="sa-stat-value"><?php echo date( 'd-m-Y', $data['last_login'] / 1000 ); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="sa-armory-actions">
                    <a href="https://worldofwarcraft.blizzard.com/en-gb/character/eu/<?php
                        echo esc_attr( $data['realm_slug'] ); ?>/<?php echo esc_attr( $data['name'] ); ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="sa-armory-btn-ext">🌐 WoW Armory</a>
                    <a href="https://raider.io/characters/eu/<?php
                        echo esc_attr( $data['realm_slug'] ); ?>/<?php echo esc_attr( $data['name'] ); ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="sa-armory-btn-ext">📊 Raider.IO</a>
                    <a href="https://www.warcraftlogs.com/character/eu/<?php
                        echo esc_attr( $data['realm_slug'] ); ?>/<?php echo esc_attr( $data['name'] ); ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="sa-armory-btn-ext">📋 WCL</a>
                </div>
            </div>
        </div><!-- .sa-armory-hero -->

        <!-- ─── GEAR PANEL ─────────────────────────────────── -->
        <div class="sa-armory-gear-section">
            <h3 class="sa-armory-section-title">⚙ Uitrusting
                <span class="sa-ilvl-badge">⭐ <?php echo number_format( $data['item_level'], 1 ); ?> ilvl</span>
            </h3>
            <div class="sa-armory-gear-grid">
            <?php foreach ( $data['equipment'] as $item ) :
                $slot_label = $SA_SLOT_LABELS[ $item['slot'] ] ?? ( '📦 ' . $item['slot'] );
                $q_class    = sa_armory_quality_class( $item['quality'] );
            ?>
                <div class="sa-armory-gear-row <?php echo esc_attr( $q_class ); ?>">
                    <span class="sa-gear-slot"><?php echo esc_html( $slot_label ); ?></span>
                    <span class="sa-gear-name"><?php echo esc_html( $item['name'] ); ?></span>
                    <span class="sa-gear-ilvl">
                        <?php if ( $item['ilvl'] ) echo (int) $item['ilvl']; ?>
                    </span>
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <div class="sa-armory-footer">
            Gesynct: <?php echo esc_html( $data['fetched'] ); ?>
            &middot; <a href="<?php echo esc_url( home_url( '/armory/' ) ); ?>">← Terug</a>
        </div>
    </div><!-- .sa-armory-wrap -->
    <?php
    return ob_get_clean();
}

// ═══════════════════════════════════════════════════════════════
// SECTIE 5 — ADMIN TAB
// ═══════════════════════════════════════════════════════════════

function sa_status_armory(): void {
    global $wpdb;
    $like  = $wpdb->esc_like( '_transient_' . SA_ARMORY_CACHE_PFX );
    $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '{$like}%'" );
    ?>
    <div class="sa-admin-module-block">
        <h3>🛡 Character Armory v<?php echo SA_ARMORY_VERSION; ?></h3>

        <table class="widefat striped" style="max-width:600px">
            <tr>
                <th>Gecachte armory pagina's</th>
                <td><?php echo $count; ?></td>
            </tr>
            <tr>
                <th>Cache TTL</th>
                <td><?php echo SA_ARMORY_CACHE_TTL / 60; ?> minuten</td>
            </tr>
            <tr>
                <th>API basis</th>
                <td><code>eu.api.blizzard.com</code> · namespace: <code>profile-eu</code></td>
            </tr>
        </table>

        <p style="margin-top:12px">
            <button class="button button-secondary" id="sa-armory-flush-all">
                🗑 Alle armory caches legen
            </button>
            <span id="sa-armory-flush-msg" style="margin-left:10px;color:green"></span>
        </p>

        <p>
            <strong>Shortcode:</strong><br>
            <code>[sa_armory]</code> — toont zoekformulier<br>
            <code>[sa_armory char="Dieouwe" realm="sporeggar"]</code> — direkt profiel
        </p>
        <p>
            <strong>URL params:</strong><br>
            <code>/armory/?char=Dieouwe&amp;realm=sporeggar</code>
        </p>

        <script>
        document.getElementById('sa-armory-flush-all')?.addEventListener('click', function() {
            this.disabled = true;
            fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=sa_armory_flush&nonce=<?php echo wp_create_nonce("sa_armory_flush_nonce"); ?>'
            })
            .then(r => r.json())
            .then(d => {
                document.getElementById('sa-armory-flush-msg').textContent =
                    d.success ? '✅ ' + d.data : '❌ Fout';
                this.disabled = false;
            });
        });
        </script>
    </div>
    <?php
}
