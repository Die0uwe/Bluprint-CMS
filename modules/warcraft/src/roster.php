<?php
/**
 * ============================================================
 * Slayer Alliance Master Suite — Guild Roster Module
 * ============================================================
 * @file        modules/roster.php
 * @version     2.0.0
 * @since       1.0.0
 * @author      DieOuwe <www.dieouwe.nl>
 * @copyright   2025 Slayer Alliance · discord.gg/y8Pu5qsEbQ
 * @license     GPL-2.0-or-later
 *
 * Shortcode:   [guild_roster]
 * Admin tab:   sa_status_roster()
 *
 * API gebruikt:
 *   GET /data/wow/guild/{realm}/{guild}/roster?namespace=profile-eu
 *   GET /profile/wow/character/{realm}/{name}/character-media?namespace=profile-eu
 *
 * Caching: WP Transients — 30 minuten voor roster, 24u voor media
 * Security: nonces, sanitize_text_field, wp_remote_get met timeout
 * ============================================================
 * Last updated: 2025-06-06
 * Status: Productie
 * ============================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Constanten ─────────────────────────────────────────────
define( 'SA_ROSTER_CACHE_KEY',      'sa_guild_roster_v2' );
define( 'SA_ROSTER_CACHE_TTL',      30 * MINUTE_IN_SECONDS );
define( 'SA_ROSTER_MEDIA_TTL',      24 * HOUR_IN_SECONDS );
define( 'SA_ROSTER_MEDIA_PREFIX',   'sa_char_media_' );
define( 'SA_ROSTER_VERSION',        '2.0.0' );

// ─── Class-kleuren mapping ────────────────────────────────────
$SA_CLASS_COLORS = [
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
 * Haalt de guild roster op via Blizzard API met transient caching.
 * Gooit GEEN PHP errors — retourneert WP_Error of array.
 *
 * @return array|WP_Error
 */
function sa_roster_fetch_data(): array|WP_Error {
    // Probeer cache
    $cached = get_transient( SA_ROSTER_CACHE_KEY );
    if ( false !== $cached ) {
        return $cached;
    }

    $core  = sa_get_core_settings();
    $token = sa_get_valid_token();

    if ( is_wp_error( $token ) || empty( $token ) ) {
        return new WP_Error( 'token_fail', 'Geen geldig Blizzard token beschikbaar.' );
    }

    $realm = sanitize_title( $core->realm ?? 'sporeggar' );
    $guild = sanitize_title( $core->guild ?? 'slayer-alliance' );

    // Guild Roster endpoint — namespace=profile-eu — token via header (aug 2024 verplichting)
    $url = "https://eu.api.blizzard.com/data/wow/guild/{$realm}/{$guild}/roster"
         . '?namespace=profile-eu&locale=en_GB';

    $response = wp_remote_get( $url, [
        'timeout' => 15,
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ],
    ] );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== (int) $code ) {
        return new WP_Error( 'api_error', "Blizzard API gaf HTTP {$code} terug voor guild roster." );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $body['members'] ) ) {
        return new WP_Error( 'no_members', 'Geen leden gevonden in API response.' );
    }

    // Verrijken: sorteer op rank → naam
    $members = $body['members'];
    usort( $members, function( $a, $b ) {
        $rankDiff = ( $a['rank'] ?? 99 ) - ( $b['rank'] ?? 99 );
        if ( $rankDiff !== 0 ) return $rankDiff;
        return strcmp(
            $a['character']['name'] ?? '',
            $b['character']['name'] ?? ''
        );
    } );

    $data = [
        'guild'   => $body['guild']['name']    ?? ucfirst( $guild ),
        'realm'   => $body['guild']['realm']['name'] ?? ucfirst( $realm ),
        'members' => $members,
        'total'   => count( $members ),
        'fetched' => current_time( 'mysql' ),
    ];

    set_transient( SA_ROSTER_CACHE_KEY, $data, SA_ROSTER_CACHE_TTL );
    return $data;
}

/**
 * Haalt character media op (avatar URL) voor een enkel lid.
 * Gecached per karakter 24 uur.
 *
 * @param string $realm Realm slug (lowercase)
 * @param string $name  Character naam
 * @return string Avatar URL of fallback placeholder
 */
function sa_roster_get_avatar( string $realm, string $name ): string {
    $cache_key = SA_ROSTER_MEDIA_PREFIX . md5( $realm . '_' . strtolower( $name ) );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) return $cached;

    $token = sa_get_valid_token();
    if ( is_wp_error( $token ) || empty( $token ) ) {
        return SA_ROSTER_PLACEHOLDER_URL;
    }

    $realm_slug = sanitize_title( $realm );
    $char_slug  = sanitize_title( $name );

    $url = "https://eu.api.blizzard.com/profile/wow/character/{$realm_slug}/{$char_slug}/character-media"
         . '?namespace=profile-eu&locale=en_GB';

    $response = wp_remote_get( $url, [
        'timeout' => 8,
        'headers' => [ 'Authorization' => 'Bearer ' . $token ],
    ] );

    if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
        return SA_ROSTER_PLACEHOLDER_URL;
    }

    $body   = json_decode( wp_remote_retrieve_body( $response ), true );
    $assets = $body['assets'] ?? [];
    $avatar = '';

    foreach ( $assets as $asset ) {
        if ( ( $asset['key'] ?? '' ) === 'avatar' ) {
            $avatar = esc_url_raw( $asset['value'] ?? '' );
            break;
        }
    }

    if ( empty( $avatar ) ) $avatar = SA_ROSTER_PLACEHOLDER_URL;

    set_transient( $cache_key, $avatar, SA_ROSTER_MEDIA_TTL );
    return $avatar;
}

// Placeholder als media niet beschikbaar is
if ( ! defined( 'SA_ROSTER_PLACEHOLDER_URL' ) ) {
    define( 'SA_ROSTER_PLACEHOLDER_URL',
        'https://render.worldofwarcraft.com/eu/character/sporeggar/0/0-avatar.jpg?alt=/wow/static/images/2d/avatar/1-0.jpg'
    );
}

// ═══════════════════════════════════════════════════════════════
// SECTIE 2 — RANK HELPER
// ═══════════════════════════════════════════════════════════════

/**
 * Vertaalt rank index naar een leesbare naam voor Slayer Alliance.
 * Aanpasbaar via filter 'sa_roster_rank_labels'.
 */
function sa_roster_rank_label( int $rank ): string {
    $defaults = [
        0 => '👑 Guild Master',
        1 => '⚔ Officer',
        2 => '🛡 Senior Member',
        3 => '⚡ Member',
        4 => '🔰 Trial',
        5 => '📦 Casual',
        6 => '🌱 Recruit',
        7 => '🔇 Alt',
        8 => '🔇 Alt',
        9 => '🔇 Alt',
    ];
    $labels = apply_filters( 'sa_roster_rank_labels', $defaults );
    return $labels[ $rank ] ?? "Rank {$rank}";
}

// ═══════════════════════════════════════════════════════════════
// SECTIE 3 — AJAX HANDLER (cache busten vanuit admin)
// ═══════════════════════════════════════════════════════════════

add_action( 'wp_ajax_sa_roster_refresh', 'sa_roster_refresh_handler' );
function sa_roster_refresh_handler(): void {
    check_ajax_referer( 'sa_roster_refresh_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Onvoldoende rechten.' );
    }
    delete_transient( SA_ROSTER_CACHE_KEY );
    wp_send_json_success( 'Roster cache gewist. Volgende paginabezoek laadt verse data.' );
}

// ═══════════════════════════════════════════════════════════════
// SECTIE 4 — SHORTCODE [guild_roster]
// ═══════════════════════════════════════════════════════════════

add_shortcode( 'guild_roster', 'sa_roster_shortcode' );

function sa_roster_shortcode( array $atts = [] ): string {
    global $SA_CLASS_COLORS;

    $atts = shortcode_atts( [
        'show_avatars'  => 'yes',   // avatars laden via API? (traag bij eerste load)
        'show_alts'     => 'yes',   // rank 7-9 tonen?
        'filter_rank'   => '',      // toon alleen rank X (leeg = allemaal)
        'per_page'      => 40,      // leden per pagina (0 = onbeperkt)
    ], $atts, 'guild_roster' );

    $show_avatars = ( $atts['show_avatars'] === 'yes' );
    $show_alts    = ( $atts['show_alts'] === 'yes' );
    $filter_rank  = is_numeric( $atts['filter_rank'] ) ? (int) $atts['filter_rank'] : null;
    $per_page     = max( 0, (int) $atts['per_page'] );

    // CSS + JS eenmalig enqueueen
    if ( ! wp_style_is( 'sa-roster', 'enqueued' ) ) {
        wp_enqueue_style(
            'sa-roster',
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/roster.css',
            [],
            SA_ROSTER_VERSION
        );
    }

    $data = sa_roster_fetch_data();

    if ( is_wp_error( $data ) ) {
        return '<div class="sa-roster-error">⚠ ' . esc_html( $data->get_error_message() ) . '</div>';
    }

    $members = $data['members'];

    // Filter alts (rank 7+)
    if ( ! $show_alts ) {
        $members = array_filter( $members, fn( $m ) => ( $m['rank'] ?? 99 ) < 7 );
    }

    // Filter op rank
    if ( null !== $filter_rank ) {
        $members = array_filter( $members, fn( $m ) => ( $m['rank'] ?? 99 ) === $filter_rank );
    }

    $members = array_values( $members );
    $total   = count( $members );

    // Paginering
    $page     = max( 1, (int) ( $_GET['roster_page'] ?? 1 ) );
    $offset   = $per_page > 0 ? ( $page - 1 ) * $per_page : 0;
    $pages    = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;
    $shown    = $per_page > 0 ? array_slice( $members, $offset, $per_page ) : $members;

    // ── Build HTML ────────────────────────────────────────────
    ob_start();
    ?>
    <div class="sa-roster-wrap">
        <div class="sa-roster-header">
            <h2 class="sa-roster-title">
                ⚔ <?php echo esc_html( $data['guild'] ); ?>
                <span class="sa-roster-realm">— <?php echo esc_html( $data['realm'] ); ?> (EU)</span>
            </h2>
            <div class="sa-roster-meta">
                <?php echo esc_html( $total ); ?> leden
                &middot; Gesynct: <?php echo esc_html( $data['fetched'] ); ?>
            </div>

            <div class="sa-roster-filters">
                <input type="text" id="sa-roster-search"
                       placeholder="🔎 Zoek lid..." class="sa-roster-search-input">
                <select id="sa-roster-class-filter" class="sa-roster-filter-select">
                    <option value="">Alle klassen</option>
                    <?php
                    $classes = array_unique( array_map(
                        fn( $m ) => $m['character']['playable_class']['name'] ?? '',
                        $members
                    ) );
                    sort( $classes );
                    foreach ( $classes as $cls ) {
                        if ( $cls ) echo '<option value="' . esc_attr( $cls ) . '">' . esc_html( $cls ) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="sa-roster-grid" id="sa-roster-grid">
        <?php foreach ( $shown as $member ) :
            $char      = $member['character'] ?? [];
            $name      = esc_html( $char['name'] ?? 'Onbekend' );
            $level     = esc_html( $char['level'] ?? '??' );
            $rank      = (int) ( $member['rank'] ?? 99 );
            $class     = esc_html( $char['playable_class']['name'] ?? 'Onbekend' );
            $race      = esc_html( $char['playable_race']['name'] ?? '' );
            $realm_s   = sanitize_title( $char['realm']['slug'] ?? 'sporeggar' );
            $color     = $SA_CLASS_COLORS[ $char['playable_class']['name'] ?? '' ]
                         ?? $SA_CLASS_COLORS['default'];
            $rank_lbl  = sa_roster_rank_label( $rank );

            // Avatar ophalen (enkel als shortcode optie aan staat)
            $avatar_url = $show_avatars
                ? sa_roster_get_avatar( $realm_s, strtolower( $char['name'] ?? '' ) )
                : SA_ROSTER_PLACEHOLDER_URL;
        ?>
            <div class="sa-roster-card"
                 data-name="<?php echo esc_attr( strtolower( $char['name'] ?? '' ) ); ?>"
                 data-class="<?php echo esc_attr( $char['playable_class']['name'] ?? '' ); ?>"
                 data-rank="<?php echo esc_attr( $rank ); ?>">

                <div class="sa-roster-card-glow" style="--class-color:<?php echo esc_attr( $color ); ?>"></div>

                <div class="sa-roster-avatar-wrap">
                    <img src="<?php echo esc_url( $avatar_url ); ?>"
                         alt="<?php echo $name; ?> avatar"
                         class="sa-roster-avatar"
                         loading="lazy"
                         onerror="this.src='<?php echo esc_url( SA_ROSTER_PLACEHOLDER_URL ); ?>'">
                    <span class="sa-roster-level"><?php echo $level; ?></span>
                </div>

                <div class="sa-roster-info">
                    <div class="sa-roster-name"><?php echo $name; ?></div>
                    <div class="sa-roster-class"
                         style="color:<?php echo esc_attr( $color ); ?>">
                        <?php echo $class; ?>
                    </div>
                    <div class="sa-roster-race"><?php echo $race; ?></div>
                    <div class="sa-roster-rank-badge rank-<?php echo $rank; ?>">
                        <?php echo esc_html( $rank_lbl ); ?>
                    </div>
                </div>

                <a href="<?php echo esc_url( home_url( "/armory/?char={$char['name']}&realm={$realm_s}" ) ); ?>"
                   class="sa-roster-armory-link" title="Bekijk Armory van <?php echo $name; ?>">
                    🛡 Armory
                </a>
            </div>
        <?php endforeach; ?>
        </div><!-- .sa-roster-grid -->

        <?php if ( $pages > 1 ) : ?>
        <div class="sa-roster-pagination">
            <?php for ( $p = 1; $p <= $pages; $p++ ) : ?>
                <a href="?roster_page=<?php echo $p; ?>"
                   class="sa-roster-page-btn <?php echo ( $p === $page ) ? 'active' : ''; ?>">
                    <?php echo $p; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div><!-- .sa-roster-wrap -->

    <script>
    (function() {
        const grid   = document.getElementById('sa-roster-grid');
        const search = document.getElementById('sa-roster-search');
        const clsFlt = document.getElementById('sa-roster-class-filter');
        if ( ! grid ) return;

        const cards = Array.from( grid.querySelectorAll('.sa-roster-card') );

        function filterCards() {
            const q   = search.value.toLowerCase().trim();
            const cls = clsFlt.value.toLowerCase();
            cards.forEach( card => {
                const nameMatch  = card.dataset.name.includes( q );
                const classMatch = !cls || card.dataset.class.toLowerCase() === cls;
                card.style.display = ( nameMatch && classMatch ) ? '' : 'none';
            });
        }

        search.addEventListener('input', filterCards);
        clsFlt.addEventListener('change', filterCards);
    })();
    </script>
    <?php
    return ob_get_clean();
}

// ═══════════════════════════════════════════════════════════════
// SECTIE 5 — ADMIN TAB
// ═══════════════════════════════════════════════════════════════

function sa_status_roster(): void {
    $data = get_transient( SA_ROSTER_CACHE_KEY );
    ?>
    <div class="sa-admin-module-block">
        <h3>⚔ Guild Roster v<?php echo SA_ROSTER_VERSION; ?></h3>

        <table class="widefat striped" style="max-width:600px">
            <tr>
                <th>Cache status</th>
                <td><?php echo $data ? '✅ Actief (' . esc_html( $data['total'] ) . ' leden)' : '❌ Geen cache'; ?></td>
            </tr>
            <tr>
                <th>Laatste sync</th>
                <td><?php echo $data ? esc_html( $data['fetched'] ) : '—'; ?></td>
            </tr>
            <tr>
                <th>Cache TTL</th>
                <td><?php echo SA_ROSTER_CACHE_TTL / 60; ?> minuten</td>
            </tr>
            <tr>
                <th>Guild</th>
                <td><?php echo $data ? esc_html( $data['guild'] ) : '—'; ?></td>
            </tr>
        </table>

        <p style="margin-top:12px">
            <button class="button button-secondary" id="sa-roster-flush-btn">
                🔄 Cache legen (force refresh)
            </button>
            <span id="sa-roster-flush-msg" style="margin-left:10px;color:green"></span>
        </p>

        <p><strong>Shortcode:</strong><br>
            <code>[guild_roster]</code><br>
            <code>[guild_roster show_avatars="yes" show_alts="no" per_page="20"]</code>
        </p>

        <script>
        document.getElementById('sa-roster-flush-btn')?.addEventListener('click', function() {
            this.disabled = true;
            fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=sa_roster_refresh&nonce=<?php echo wp_create_nonce("sa_roster_refresh_nonce"); ?>'
            })
            .then(r => r.json())
            .then(d => {
                document.getElementById('sa-roster-flush-msg').textContent =
                    d.success ? '✅ ' + d.data : '❌ ' + (d.data || 'Fout');
                this.disabled = false;
            });
        });
        </script>
    </div>
    <?php
}
