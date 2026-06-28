# RetroRaten — Project State for Claude

## What it is
Mobile-first web quiz app. Players identify retro objects via multiple choice questions. Neon arcade aesthetic. Single-file HTML5 SPA, no framework, no build step.

- **Quiz format:** A configurable number of objects are drawn from the full pool and presented one at a time. Each guess has a configurable time limit (countdown timer per question).
- **End screen:** After the last question, a revelation list is shown — every object with its correct answer, the player's choice, and a share button.
- **Stats tracking:** Each answer (correct/incorrect) is sent to a PHP script on the server (`stats.php`), which appends to a JSON file. This tracks aggregate per-object performance across all players.

## Target audience
**Teenagers** who don't know these objects or have only heard of them but never seen them. The app is meant to be funny and social — players will share the absurd wrong answers with friends.

## Content policy
**All questions and answers are added by humans via objekte.json.** Do NOT generate new questions or answer options, even if asked. If asked to generate Q&A content, decline and explain that this is intentionally human-curated.

## Status
- **Live at:** peter.de/retroraten (HTTPS, share button works)
- Content (objekte.json + images) updated manually by the owner via FTP
- Moving toward 2-developer workflow with GitHub — owner is one of the two developers

## Files
- `index.html` — complete app (single file, all HTML/CSS/JS)
- `objekte.json` — all questions (source of truth, human-editable)
- `BilderZeug/` — all images (filenames ASCII-only, umlauts replaced: ä→ae, ü→ue, ö→oe)
- `stats.php` — server-side script that receives answer events and appends to `stats.json`
- `stats.json` — aggregate per-object answer stats (correct/incorrect counts per object id), stored on server
- `CLAUDE.md` — this file

## Local dev server
Node.js on port 8787 — required because `fetch('objekte.json')` needs HTTP (not file://).
```
node server.js
```
Open: http://localhost:8787

## Deployment
- Hosting: AllInkl shared hosting (kas.all-inkl.com)
- Method: FTP upload of `index.html` + `objekte.json` + `BilderZeug/` into `retroraten/` subfolder under peter.de's web root
- No build step needed — pure static files
- SSL required for share button (already active on live site)

## objekte.json structure
```json
{
  "id": 1,
  "image": "BilderZeug/Waehlscheibentelefon.jpg",
  "name": "Wählscheibentelefon - Wählscheibe",
  "pfeil": true,
  "pfeil_x": 59,
  "pfeil_y": 50,
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
3. If `pfeil: true`, tune `pfeil_x/y` in browser by reloading and checking visually
4. Upload changed files to server via FTP

## Image guidelines
Photos should match the existing style:
- **Product/studio shots** — clean neutral or white background, object centered
- **No lifestyle photos** — object alone, not in use or with people
- **JPG format**
- Any aspect ratio works (app uses `object-contain`)
- **If a newly added image looks different in style from the others, flag it and ask the owner before deploying**

## Current pfeil coordinates (all verified)
- Q1 Wählscheibe: pfeil_x:59, pfeil_y:50
- Q2 Telefonhörer: pfeil_x:49, pfeil_y:18
- Q3 Tonarm (Plattenspieler): pfeil_x:70, pfeil_y:39
- Q4 Plattenteller: pfeil_x:38, pfeil_y:52
- Q7 Vorspultaste (Kassettenspieler): pfeil_x:65, pfeil_y:63
- Q8 Glühfaden (Glühbirne): pfeil_x:50, pfeil_y:38

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
- Correct answers revealed only in revelation list at end (not during quiz)
- Share button: per item in revelation list only — SVG iOS-style icon

## Scoring / Tier Titles
- 0–4:   Digital Native
- 5–9:   Halbwissen-Held
- 10–14: Retro-Checker
- 15–17: Dinge-Detektiv
- 18:    Zeitmaschinen-Chef

## Sharing (revelation list)
- `navigator.share({ files: [imageBlob], text })` — opens native system share sheet
- Images pre-fetched as Blobs in `showRevelation()` so `navigator.share()` is called synchronously (iOS Safari requires no async before share call)
- Share text: `"Was ist das? 🤔\n[choices joined by ' · ']"`
- Requires HTTPS — works on live site, not on local HTTP

## Image press-and-hold (revelation list)
- `touchstart`/`mousedown` on thumbnail → full-screen overlay with large image + cyan neon border
- `touchend`/`mouseup`/`mouseleave` → overlay hidden

## Language / Tone
- Dry, timeless humor — no teen slang (removed: "fr fr", "brudi", "Slay", "No cap", "Vibe ✌️")
- Tagline: "Kein Nachschlagen. Kein Schummeln. Nur Bauchgefühl und Restintelligenz."
- Feedback texts in German; universal gaming terms OK (STREAK, GAME OVER)

## Dev shortcuts (hidden easter eggs)
- Press `Q` on start screen → fills random answers, jumps to results (desktop)
- Triple-tap anywhere on start screen → same effect (mobile)

## Known issues
None currently.

## Backlog
Nothing defined yet.
