# NDTV on WordPress VIP: Playground demo

A shallow replica of ndtv.com on WordPress, built for NDTV's technical and editorial teams. It runs on WordPress Playground. It uses the NDTV name, logo, palette and section structure.

- **Videos are NDTV's own.** 37 videos (VOD, 9:16 shorts and the 24x7, India and Profit live channels) are synced from a snapshot of NDTV's video feed. They stream from NDTV's CDNs; no files are copied.
- **Stories are placeholder copy** written for the demo.

**Status:**
- Phase 1: theme, plugin, video CPT, player block, header/footer, homepage zones, seeder, blueprint.
- Phase 2: `/video/` hub, single video page, shorts rail, related videos, video rails.
- Video system: NDTV feed sync, HLS live playback, remote posters, hover previews, "NDTV source" editor panel, public Playground link.

## Try it

Open the demo on WordPress Playground (no install; you're logged in as `admin`):

`https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/<owner>/<repo>/main/blueprint.public.json`

Places to look:
- `/video/`: the video hub. Hover a tile to see its preview clip. The NDTV Channels row plays live.
- Any video page, for example `/video/karnataka-final-voter-list-down-by-nearly-1-crore-after-special-revision-1181320/`. The URL matches ndtv.com.
- **Videos → NDTV Sync** in wp-admin: the feed, the last sync and a "Sync now" button.
- Edit any video: the **NDTV source** panel in the sidebar shows the synced metadata.

## Run it

```bash
bin/playground-bundle            # builds dist/ndtv-playground.zip (theme + plugin + blueprint.json)
bin/playground-bundle --serve    # builds, then boots it at http://127.0.0.1:9400 (PORT=… to change)
```

The blueprint logs you in as `admin` / `password`, sets the timezone to Asia/Kolkata and permalinks to `/%category%/%postname%/`, installs the theme and plugin, and seeds the content.

To publish a share link, push this repo to a public GitHub repository, then run:

```bash
GITHUB_REPO=<owner>/<repo> bin/playground-bundle
```

This writes `blueprint.public.json`. It is the same blueprint, but it installs the theme and plugin straight from the repo (`git:directory` resources) instead of the bundled zips. Commit it and push. The script prints the share link. Playground needs `features.networking`, which is on, because the player streams from NDTV's CDNs.

## What's in it

### `themes/ndtv`: block theme

- `theme.json`: NDTV tokens (red `#E21B22`, ink, text, grey, panel, rule, plus a dark "night" for the video UI). Inter is used for UI and Noto Serif 500 for headlines. Both fonts are self-hosted from `assets/fonts`.
- Templates: `front-page`, `single`, `archive`, `index`, `search`, `page`, `404`.
- Video templates, all in the dark video UI with a left rail: `archive-ndtv_video` (the `/video/` hub), `single-ndtv_video`, `taxonomy-video_category`, `taxonomy-video_category-shorts` (a 9:16 grid) and `taxonomy-video_show`.
- Parts: `header`, `footer`, `sidebar` (the right rail), `video-nav` (the video left rail).
- Patterns:
  - `masthead`: logo, nav, market strip.
  - `quick-links`
  - `ask-bar`: the askNDTV prompt, styled. It runs a normal site search.
  - `home-lead`: a sticky lead story and two columns of three.
  - `home-four-up`
  - `right-rail`: MPU, sponsored native, Watch Live, Other Stories, NDTV Micro, Top Deals.
  - `videos-band`: channel tabs, a featured player and Must Watch.
  - `home-sections`: section blocks two per row, then a four-up row.
  - `footer`
  - `video-hub`: the `/video/` page. It has a Latest Videos carousel, round NDTV Channels LIVE badges, a Shorts carousel, Top Videos, category tabs with a band per category (description and a "More … Videos" button) and a Shows grid.
  - `video-nav`: search limited to videos, plus Live TV, Latest, Shorts, Categories, Shows, Top Videos and NDTV Classics.
- `assets/js/carousel.js`: prev/next buttons for the carousels. It only loads on video pages, and the rails still scroll natively without it.
- `inc/sections.php`: Query Loops need term and post IDs, and those only exist after seeding. So the homepage patterns are PHP and build core Query Loop markup at render time. Everything in the editor is still core blocks.

### `plugins/ndtv-core`: content model and blocks (no build step)

- **`ndtv_video` CPT**, archive at `/video/`, single videos at `/video/<slug>-<ndtv id>/` (the same path as ndtv.com).
  - Meta, all `show_in_rest`. The sync writes it; the files stay on NDTV's backend.
    - `player_id`: NDTV's video ID (`1181320`, or `LIVE_BG24x7` for a channel).
    - `stream_url`: HLS `master.m3u8`.
    - `mp4_url`: progressive MP4 rendition.
    - `preview_url`: the short muted clip played on tile hover.
    - `duration` (seconds), `orientation` (`horizontal` or `vertical`), `kind` (`vod` or `live`), `channel`.
    - `content_type` (`clip`, `full` or `live`), `video_from` (`TV` or `Digital`), `source_url` (the ndtv.com page), `related_ids` (the player's "up next" IDs), `synced_at`.
  - Taxonomies, kept separate from news categories:
    - `video_category`: News, Shorts, Sports, Business, Entertainment, Auto, Exclusive, Features.
    - `video_show`: the show is a taxonomy rather than meta, so shows get archive pages.
- **NDTV video sync** (`inc/video-sync.php`):
  - It reads NDTV's own feed shape: the `{"total","results":[…]}` MRSS-style items served by `ndtv.com/shorts/api/index1`, plus `player:*` fields from the page player config.
  - It upserts on `player_id` and keeps NDTV's slug. Feed keywords become tags (internal asset codes are dropped), `media:category` becomes the video category (vertical items go to Shorts), and `media:show` becomes the show.
  - On Playground the feed is the bundled snapshot `data/ndtv-video-feed.json`. On VIP, hook `ndtv_core_video_feed` to call NDTV's API from cron.
  - Run it from **Videos → NDTV Sync** or with `wp ndtv sync-videos [--file=feed.json]`.
- **Remote posters** (`inc/remote-media.php`): each poster is a real attachment, so featured images, Query Loops, REST and the media library all work. Its URLs point at `c.ndtvimg.com`, and sizes use the CDN's `?downsize=W:H` resizer.
- **`ndtv/player`** is a dynamic block. It plays a library video or a raw player ID. Inside a video Query Loop or a single video, it plays the current video. The editor sidebar has a searchable picker over the CPT (the "media-library-style selector" idea).
  - VOD plays NDTV's MP4 rendition. Live channels have only HLS: `assets/js/player.js` plays them natively in Safari and Chrome, and loads the bundled hls.js 1.5.17 on demand elsewhere.
  - Videos with no stream (local uploads) fall back to a bundled sample MP4 that matches their orientation. The `ndtv_core_fallback_video_src` filter controls the fallback.
  - Video tiles with a `preview_url` play it on hover, as on ndtv.com.
- **"NDTV source" panel**: the video editor sidebar shows the synced metadata read-only, with a link to the ndtv.com page.
- **`ndtv/badge`** shows the duration, LIVE, play, section name or channel for the current post. Use it in loops.
- **Video rails** (`inc/rails.php`) are core Query Loop variations, found in the inserter as "NDTV: …". A loop opts in with `query.ndtvRail`, and `query_loop_block_query_vars` applies the rail on the front end. Editor previews show the newest videos.
  - `latest`: horizontal video on demand (VOD), newest first.
  - `shorts`: vertical videos.
  - `live`: live channels.
  - `top`: most viewed. The `ndtv_core_top_video_ids` filter is the Parse.ly hook. Without it, the rail sorts by the seeded `ndtv_views` meta.
  - `related`: fills to the rail's size. Videos that share a tag with the current story or video come first, then the same video category, then the latest videos.
  - On a single video page, the rails never list the video being watched.
- **Related videos on stories:** `single.html` has a Related Videos rail under the tags.
- **`ndtv/market-strip`** shows gold and silver rates, date and time in IST, weather and AQI, the network grid and login. Values are static on the demo. In production they would come through the `ndtv_core_market_data` filter, fed by a cached rates/weather feed.
- **Kicker badge:** story meta `ndtv_kicker` ("3rd T20I:") and `ndtv_scorecard_url` are injected into every `core/post-title` by a render filter. Templates keep using core blocks. Add the class `no-kicker` to a title block to opt it out.
- **Seeder** (`inc/seeder.php`, `inc/seed-data.php`):
  - 5 authors.
  - 12 sections with 51 stories. One is sticky as the lead.
  - Descriptions for the 8 video categories, then the NDTV video sync (37 videos, with a stub view count for each).
  - 16 placeholder story images. Every seeded item, including the remote poster attachments, is tagged `_ndtv_demo`.

```bash
wp ndtv seed            # no-op if already seeded
wp ndtv seed --force    # delete seeded content and reseed
wp ndtv unseed
wp ndtv sync-videos     # re-run the NDTV video sync
```

## NDTV's video system (what the public site shows)

Captured from ndtv.com's public pages on 2026-10-27 with a normal browser. The site and image CDN return 403 to non-browser clients.

- **One video library across the network.** NDTV uses a single numeric ID space across ndtv.com, ndtv.in, sports.ndtv.com, gadgets360.com and ndtvprofit.com. Video pages are `/video/<slug>-<id>`. Shorts are a separate Remix app at `/shorts/<slug>-<id>`.
- **Player config.** Every video page embeds `window.__html5playerdata`: `id`, `isLive`, `title`, `dur`, `media` (HLS), `media_mp4`, `image`, `category`, `show`, `channel` (1 = 24x7, 3 = Profit), `content_type` (clip or full), `video_from` (TV or Digital), `adpoint` (preroll/midroll), sprite thumbnails, chapters, cue points and `video_related_videos`. Pages also carry schema.org `VideoObject` JSON-LD.
- **Feed API.** `/shorts/api/index1?pageNumber=1&language=1` returns MRSS-style items: `media:filepath`, `media:allfileformats` (HLS and MP4), `media:category`, `media:show`, `media:keywords`, `media:preview`, `vertical`, `media:network` and more. The sync models this shape. (The live feed includes an API key per item. The snapshot strips it.)
- **Delivery.** VOD is on the Bitgravity CDN (`ndtvod.pc.cdn.bitgravity.com`, HLS and MP4, `Access-Control-Allow-Origin: *`). Live channels are Akamai HLS with Google DAI ad insertion. Posters are on `c.ndtvimg.com`, which resizes with `?downsize=W:H` and blocks cross-origin `fetch` but allows `<img>`.
- **On VIP:** the same CPT and meta, fed by NDTV's API on a cron. `player_id` is the join key, so NDTV's own player (IMA/DAI ads, analytics) can take over the `ndtv/player` markup without changing the content model.

## Video-only product

A video-only site (an "NDTV Videos" app or domain) would be a multisite sub-site. It would run this same theme, plugin and `ndtv_video` CPT, with `/video/` as its front page. The content model, rails and player don't change. Only the front-page template does.

## Verified in Phase 2

- These return 200 with no PHP notices: `/video/`, a live channel, a VOD video, a short, a video category, the shorts category, a show and a video-only search.
- Each rail returns the right set:
  - latest has no live channels or shorts
  - live returns the 3 channels
  - shorts returns 6 vertical videos
  - top is sorted by views
  - related on a cricket story leads with "Series Wrap"
  - related on an AQI story leads with "Prime Debate"
  - the current video is excluded on its own page
- Carousel items line up and the next button scrolls. Nothing overflows at 390px on the hub, a video, a short, the shorts grid or a story.
- Site Editor: all 12 templates and 4 parts parse with 0 invalid blocks, and the five rail variations are registered.

## Verified in Phase 1

- The blueprint boots on Playground CLI (PHP 8.3, WP latest) and seeds with no PHP warnings.
- These return 200 with no PHP notices: home, single story, single video, category, tag, year archive, search, `/video/` and the shorts video category.
- Site Editor: the front-page template parses 281 blocks with 0 invalid.
- Post editor: `ndtv/player` is valid, the picker lists all 24 videos, the server preview renders, and video meta is exposed in REST.
- Layout checked at 1440px and 390px.

## Next

3. Spotlight template routing, gallery, live blog, web stories.
4. Guardrails, "already covered?" panel, ZeroGPT, AI flag, Co-Authors Plus.
5. Final bundle, block validation pass, screenshots, share link.
