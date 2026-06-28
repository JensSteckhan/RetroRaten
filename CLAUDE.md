# RetroRaten — Project State for Claude

## What it is
Mobile-first web quiz app. Players identify retro objects via multiple choice questions. Neon arcade aesthetic. Single-file HTML5 SPA, no framework, no build step.

- **Quiz format:** A configurable number of objects (`config.json` → `roundSize`) are drawn from the full pool and presented one at a time. Each guess has a 15-second countdown timer (`TIMER_DURATION` in `index.html`, currently hardcoded, not configurable via `config.json`).
- **End screen:** After the last question, a revelation list is shown — every object with its correct answer, the player's choice, and a share button. The "play again" button reads `"{n} MEHR!"` (n = unseen objects still left in the pool) until the full pool has been cycled through, then it reads `"NOCHMAL"` and the cycle restarts.
- **Stats tracking:** Each answer (correct/incorrect) is sent to a PHP script on the server (`stats.php`), which appends to a JSON file. This tracks aggregate per-object performance across all players.

## Target audience
**Teenagers** who don't know these objects or have only heard of them but never seen them. The app is meant to be funny and social — players will share the absurd wrong answers with friends.

## Content policy
**All questions and answers are added by humans via objekte.json.** Do NOT generate new questions or answer options, even if asked. If asked to generate Q&A content, decline and explain that this is intentionally human-curated.

## Status
- **Live at:** peter.de/retroraten (HTTPS, share button works)
- Content (objekte.json + images) updated manually by the owner via FTP
- **Multiple developers** work independently, each with their own Claude Code session/local copy. Finished changes get uploaded to a shared location (currently GitHub) for distribution — the specific sync mechanism isn't load-bearing, it could just as well be a plain file server. Don't assume a local git repo exists; check before relying on git state.

## Files
- `index.html` — complete app (single file, all HTML/CSS/JS)
- `objekte.json` — all questions (source of truth, human-editable)
- `config.json` — round size + impressum link toggle (see below)
- `BilderZeug/` — all images (filenames ASCII-only, umlauts replaced: ä→ae, ü→ue, ö→oe)
- `stats.php` — server-side script that receives answer events and appends to `stats.json`
- `stats.json` — aggregate per-object answer stats (correct/incorrect counts per object id), stored on server
- `server.js` — local dev-only static file server (not deployed)
- `coin-insert.mp3` — coin sound effect, plays on tapping "INSERT COIN" on the start screen
- `CLAUDE.md` — this file

## Local dev server
Node.js on port 8787 — required because `fetch('objekte.json')` needs HTTP (not file://).
```
node server.js
```
Open: http://localhost:8787
- Strips query strings before resolving file paths (needed for `?calibrate=<id>`, see "Pfeil calibration mode")
- Serves `.html`, `.json`, `.jpg`, `.png`, `.svg`, `.mp3` with correct `Content-Type`
- Binds to `0.0.0.0`, so it's reachable from other devices on the same network, not just localhost — has a path-traversal guard (rejects requests resolving outside the project root) since it's network-reachable while running
- **Cannot run `stats.php`** (no PHP runtime) — calls to it fail silently (caught in `submitStats()`/`showRevelation()`), so stats just don't persist locally. Not a bug, just a local-dev limitation.

## Deployment
- Hosting: AllInkl shared hosting (kas.all-inkl.com), PHP available
- Method: FTP upload into `retroraten/` subfolder under peter.de's web root
- Required files: `index.html`, `objekte.json`, `config.json`, `BilderZeug/`, `coin-insert.mp3`, `stats.php`
- Optional: `stats.json`/`stats.lock` — `stats.php` creates them itself on first write if missing; only upload `stats.json` if you want to carry over existing stats
- Do NOT upload: `server.js`, `.gitignore`, `README.md`, `CLAUDE.md` — local-dev/repo-only files
- The `retroraten/` folder must be writable by PHP, otherwise `stats.php` can't create/update `stats.json`/`stats.lock`
- No build step needed — pure static files (+ one PHP script)
- SSL required for share button (already active on live site)

## config.json structure
```json
{
  "roundSize": 15,
  "showImpressumLink": false
}
```
- `roundSize` — how many objects are drawn per round from the full `objekte.json` pool. Rounds rotate through unseen objects first (via `localStorage`), so everyone gets shown eventually before repeats.
- `showImpressumLink` — toggles a footer link to `impressum.html` on the start screen. **`impressum.html` does not currently exist in this project** — keep this `false` until that file is added, otherwise the link 404s.

## objekte.json structure
```json
{
  "id": 1,
  "image": "BilderZeug/Waehlscheibentelefon.jpg",
  "name": "Wählscheibentelefon - Wählscheibe",
  "pfeil": true,
  "pfeil_x": 68.8,
  "pfeil_y": 48.0,
  "choices": ["Wählscheibe", "Nummernkreis", "Ziffernpicker", "Lochscheibe", "Lochkarte"],
  "correct_answer": "Wählscheibe"
}
```
- `pfeil: false` → just `name`, no `pfeil_x/y` needed
- `pfeil: true` → arrow points at specific part; name format: "Gerät - Teil" (e.g. "Plattenspieler - Tonarm")
- `pfeil_x/y` = percentage coordinates (0–100); the dot sits AT the target, chevron floats above
- Max 5 choices per question (mobile layout constraint)
- `correct_answer` must exactly match one entry in `choices`

## How to add a new object
1. Add image to `BilderZeug/` — JPG, ASCII filename (replace ä→ae, ü→ue, ö→oe, ß→ss)
2. Add entry to `objekte.json` with next available `id`
3. If `pfeil: true`, tune `pfeil_x/y` using the built-in calibration mode (see below) instead of guessing
4. Upload changed files to server via FTP

## Pfeil calibration mode
Built into `index.html` for setting/correcting `pfeil_x`/`pfeil_y` precisely and fast — no need to play through the quiz to reach a specific object.
- Open `http://localhost:8787?calibrate=<id>` (e.g. `?calibrate=7`) — jumps directly to that object, timer disabled, answer buttons inert
- Click anywhere on the image → marker jumps there live, exact percentages shown in the on-screen readout box
- "◀ Zurück" / "Weiter ▶" buttons step through all objects in `objekte.json` without re-typing the URL
- Read the coordinates off the readout and enter them into `objekte.json` manually (calibration mode does not write the file itself)

## Image guidelines
Photos should match the existing style:
- **Product/studio shots** — clean neutral or white background, object centered
- **No lifestyle photos** — object alone, not in use or with people
- **JPG format**
- **600×400 px** (3:2) — the image card's `aspect-ratio` is hardcoded to match this exactly so `object-contain` shows no letterboxing. Older images not yet resized to 600×400 will show black bars until updated.
- **If a newly added image looks different in style from the others, flag it and ask the owner before deploying**

## Current pfeil coordinates
Recalibrated for the 600×400 image format (see "Pfeil calibration mode" above):
- Q1 Wählscheibe: pfeil_x:68.8, pfeil_y:48.0 — verified with calibration mode
- Q2 Telefonhörer: pfeil_x:55, pfeil_y:20 — rough estimate only, **not yet re-verified** with calibration mode
- Q3 Tonarm (Plattenspieler): pfeil_x:65.5, pfeil_y:44.9 — verified with calibration mode
- Q4 Plattenteller: pfeil_x:41, pfeil_y:46 — rough estimate only, **not yet re-verified** with calibration mode
- Q7 Vorspultaste (Kassettenspieler): pfeil_x:63.6, pfeil_y:62.1 — verified with calibration mode
- Q8 Glühfaden (Glühbirne): pfeil_x:50, pfeil_y:38 — verified with calibration mode

## Design system
CSS variables in `index.html`:
```css
--neon-pink:   #ff2d78
--neon-cyan:   #00f5ff
--neon-yellow: #ffe600
--neon-green:  #39ff14
--dark-bg:     #0a0a1a   (page background)
--card-bg:     #12122a   (card/button background)
```
Fonts: `Press Start 2P` (retro pixel, headers/labels), `Inter` (answer buttons, body text)
Background: dark grid pattern with subtle cyan lines

## Key UX decisions
- No "Was ist das?" label — removed to save space
- Pfeil warning banner: single line "⚠️ Worauf zeigt der Pfeil im Bild?"
- Answer buttons: `padding: 8px 14px`, gap between buttons `gap-2` (compact for mobile, fits 5 answers without scrolling)
- `@media (hover: hover)` on button hover — prevents iOS sticky-hover bug after tap
- Max 5 answers — fits on mobile without scrolling even with pfeil banner
- Correct answers revealed only in revelation list at end (not during quiz) — this includes timeouts: letting the timer run out does **not** highlight the correct answer either, only locks the buttons
- Share button: per item in revelation list only — SVG iOS-style icon
- Tapping/clicking "INSERT COIN" on the start screen plays `coin-insert.mp3`
- Start screen text shows two different counts: the flavor text ("N Objekte...") shows the **total pool size**, the badge ("🎮 N FRAGEN") shows the **round size** (`config.roundSize`) — intentionally different numbers

## Robustness
- If `objekte.json` is missing or fails to parse (e.g. invalid JSON), the start screen shows a red error card and hides the normal start content/button instead of letting the player start a broken round (see `init()` in `index.html`)

## Scoring / Tier Titles
Scaled to `roundSize: 15` (max possible score). **If `roundSize` changes, these thresholds must be rescaled too** — they're a fixed array in `index.html` (`TIER_TITLES`), not computed from `config.json`.
- 0–3:   Digital Native
- 4–7:   Halbwissen-Held
- 8–11:  Retro-Checker
- 12–14: Dinge-Detektiv
- 15:    Zeitmaschinen-Chef

## Sharing (revelation list)
- `navigator.share({ files: [imageBlob], text })` — opens native system share sheet
- Images pre-fetched as Blobs in `showRevelation()` so `navigator.share()` is called synchronously (iOS Safari requires no async before share call)
- Share text: `"Was ist das? 🤔\n[choices joined by ' · ']"`
- Requires HTTPS — works on live site, not on local HTTP

## Image press-and-hold (revelation list)
- `touchstart`/`mousedown` on thumbnail → full-screen overlay with large image + cyan neon border
- `touchend` (per thumbnail) / `mouseup` (global on `document`, not per-thumbnail) → overlay hidden
  - Must be global: once the overlay covers the thumbnail, the browser fires `mouseleave` on it immediately (hover target changed), so a per-thumbnail `mouseup`/`mouseleave` closes the overlay instantly on desktop. A document-level `mouseup` avoids this.

## Language / Tone
- Dry, timeless humor — no teen slang (removed: "fr fr", "brudi", "Slay", "No cap", "Vibe ✌️")
- Tagline: "Kein Nachschlagen. Kein Schummeln. Nur Bauchgefühl und Restintelligenz."
- Feedback texts in German; universal gaming terms OK (STREAK, GAME OVER)
- Streak feedback (`FEEDBACK.streak_win`/`streak_lose`) only has fixed milestone messages up to streak 6. Streaks ≥6 reuse the streak-6 emoji but combine it with a random pick from the regular `correct`/`wrong` bag (via `pickBag()`) so the text doesn't repeat verbatim on long streaks

## Dev shortcuts (hidden easter eggs)
- Press `Q` on start screen → fills random answers, jumps to results (desktop)
- Triple-tap anywhere on start screen → same effect (mobile)

## Known issues
None currently.

## Backlog
Nothing defined yet.
