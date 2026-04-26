# Blacksmith Forge UI Refresh Implementation Plan

Source feedback date: 2026-04-26

This plan builds on `UI_MOCKUP_IMPLEMENTATION_GUIDE.md`. The current UI has the right theme direction, but it still behaves like a web interface. The next pass should make the forge feel physical: the player should click fire, spend coins, inspect materials, and serve customers through interactions that feel like a workshop.

## Design Goal

Move from themed panels to a living forge workspace.

Priorities:

- Make the forge action the dominant interaction on the first screen.
- Turn navigation into workshop locations instead of browser-style tabs.
- Establish clear visual hierarchy so Forge is always the primary focal point.
- Add reward feedback for crafting, spending, gaining gold, reputation changes, and inventory growth.
- Make customers, recipes, upgrades, and materials feel like game objects with intent and progression.

## Implementation Principles

- Keep backend/API contracts unchanged for this refresh.
- Reuse current React state, stores, services, and loaded API data.
- Implement feedback with CSS/React state first. Add heavier effects only if they are cheap and maintainable.
- Respect reduced-motion preferences with `@media (prefers-reduced-motion: reduce)`.
- Use temp assets under `frontend/public/temp-assets/blacksmith/` until final art exists.
- Keep the UI responsive; the forge remains first visually on mobile and desktop.

## Phase 1: Forge As Centerpiece

Problem: `Light the Forge` currently reads as a normal button.

Implementation:

- Replace the current primary forge CTA with a large forge centerpiece in `ForgePreviewPanel`.
- Make the fire/anvil area clickable, with the button label integrated into the forge object.
- Add ember glow, flicker, and short heat-pulse animation when crafting starts.
- Add transient result feedback near the forge: success, failed craft, quality, and gold gained.
- Keep keyboard accessibility by preserving a real `button` element inside the visual forge control.

Likely files:

- `frontend/src/components/dashboard/ForgePreviewPanel.tsx`
- `frontend/src/components/ui/FramedButton.tsx`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- The forge action is the largest and brightest element on the Forge screen.
- The player can click the fire/forge visual, not only a small text button.
- Crafting creates visible immediate feedback without layout shift.

## Phase 2: Workshop Navigation

Problem: navigation still feels like web tabs.

Implementation:

- Restyle `ForgeNavigation` as workshop stations:
  - Forge: anvil/fire station
  - Recipes: open book
  - Materials: crate/market stall
  - Customers: notice board
  - Upgrades: workbench
- Change active state from tab underline to station glow, warm rim light, and slight depth.
- Add hover warmth with orange glow and small icon lift.
- Keep current tab keys and routing behavior.
- Consider replacing emoji with temp image icons:
  - `nav-forge.png`
  - `nav-recipes.png`
  - `nav-materials.png`
  - `nav-customers.png`
  - `nav-upgrades.png`

Likely files:

- `frontend/src/components/layout/ForgeNavigation.tsx`
- `frontend/src/data/ui-assets.ts`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- Navigation reads as physical workshop destinations.
- Active destination is obvious without relying only on text.
- Hover/focus states are warm, visible, and accessible.

## Phase 3: Visual Hierarchy Pass

Problem: panels have equal weight, flattening importance.

Implementation:

- Rebalance the Forge dashboard layout:
  - Forge panel: largest, centered, brightest, layered above surrounding panels.
  - Customers: secondary, close to forge as incoming work.
  - Inventory: tertiary support panel.
  - Market, recipes, upgrades: lower priority workshop tools.
- Add depth tokens:
  - `--forge-depth-primary`
  - `--forge-depth-secondary`
  - `--forge-depth-ambient`
- Create panel variants for `OrnatePanel`: `primary`, `secondary`, `tertiary`.
- Avoid nested-card appearance; use framed sections and rows.

Likely files:

- `frontend/src/components/dashboard/ForgeDashboard.tsx`
- `frontend/src/components/ui/OrnatePanel.tsx`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- First eye landing point is the forge centerpiece.
- Secondary panels support the forge instead of competing with it.
- The dashboard remains readable at desktop and mobile widths.

## Phase 4: Resource Feedback

Problem: gold, reputation, and level feel detached from the game world.

Implementation:

- Restyle resource stats as physical objects:
  - Gold: coin pouch or stacked coins.
  - Reputation: crest/shield.
  - Level: forge mark or rank plate.
- Add change detection in the top bar so resource gains/losses animate briefly.
- Show floating deltas near the relevant resource: `+10g`, `+1 Reputation`.
- Add coin jiggle on gold gain and shield glow on reputation gain.
- Store only previous displayed values in component state; do not add persistence.

Likely files:

- `frontend/src/components/layout/ForgeTopBar.tsx`
- `frontend/src/components/ui/ResourceStat.tsx`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- Resource changes are visible within one second of state update.
- Feedback is subtle and does not block interaction.
- Reduced-motion users still get a non-animated highlight state.

## Phase 5: Inventory Chest

Problem: inventory is visually styled but emotionally empty.

Implementation:

- Convert inventory preview into a chest or shelf view with visible item stacks.
- Show material icons with counts and pile intensity:
  - 0: empty slot
  - low: single item
  - medium: small stack
  - high: full pile
- Add hover/focus tooltip with material name, quantity, and use context.
- Add optional quick actions where gameplay supports it:
  - quick sell only if backend/service supports selling
  - quick use only for valid crafting context
- Do not fake actions that do not exist yet; use disabled affordances with tooltips if needed.

Likely files:

- `frontend/src/components/dashboard/InventoryPreviewPanel.tsx`
- `frontend/src/components/features/MaterialsTab.tsx`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- Inventory looks like a growing collection, not a stat list.
- Item counts remain clear.
- Tooltips work with mouse and keyboard focus.

## Phase 6: Customer Personality

Problem: customers currently read as rows of data.

Implementation:

- Add a customer presentation mapper that enriches backend customer data with UI-only flavor:
  - portrait/icon variant
  - short request line
  - intent icon and color
- Suggested intent mapping:
  - durability: shield, blue/steel
  - value: coin, gold
  - quality: crown/spark, violet/gold
- Display customer cards/rows as people arriving at the forge:
  - portrait/crest
  - name
  - request line
  - budget
  - reputation effect
  - intent badge
- Keep backend customer fields as source of truth; flavor lines can live in frontend data until the API supports them.

Likely files:

- `frontend/src/components/dashboard/CustomerPreviewPanel.tsx`
- `frontend/src/components/features/CustomersTab.tsx`
- `frontend/src/data/customer-flavor.ts`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- Each customer has personality beyond name and budget.
- Intent is scannable by icon and color.
- Existing customer API responses still render if flavor data is missing.

## Phase 7: Materials Market Urgency

Problem: the materials screen is clean but emotionally flat.

Implementation:

- Add price mood styling:
  - cheap: green/teal accent
  - normal: gold/neutral
  - expensive: red/orange accent
- Add supply indicators using available data where possible. If API has no supply field, use static UI labels sparingly or defer true supply mechanics.
- Add purchase feedback:
  - coin flyout from gold area or button
  - material icon slides toward inventory
  - bought row briefly glows
- Avoid implying real price fluctuation unless values actually change.

Likely files:

- `frontend/src/components/features/MaterialsTab.tsx`
- `frontend/src/components/dashboard/MarketPreviewPanel.tsx`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- Purchase interactions feel transactional.
- Price states are visually distinct and readable.
- UI does not misrepresent static data as dynamic market simulation.

## Phase 8: Recipe Crafting Loop

Problem: recipes are informative but passive.

Implementation:

- Add a craft CTA to each recipe where existing crafting action supports the recipe.
- Show material progress bars instead of only missing red rows.
- Add readiness states:
  - ready: warm glow and active craft CTA
  - close: partial bars with "near ready" styling
  - blocked: muted/locked styling
- Show quality or success preview if the frontend has enough data. Otherwise, display material readiness only.
- Animate requirement rows when a recipe becomes craftable.

Likely files:

- `frontend/src/components/features/RecipeCard.tsx`
- `frontend/src/components/features/RecipesTab.tsx`
- `frontend/src/hooks/useAPI.ts`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- The player can tell which recipes are craftable at a glance.
- Material progress is visible without reading every count.
- Crafting from recipe cards uses existing API behavior.

## Phase 9: Upgrade Milestones

Problem: upgrades feel like data cards with prices.

Implementation:

- Rewrite upgrade labels into player-facing milestone copy:
  - `Forge burns 25% hotter`
  - `+15% crafting success`
  - `Customers pay better prices`
- Add locked, available, and purchased states.
- Show tiered progression visually with notches, runes, or workbench slots.
- Add upgrade purchase feedback:
  - workbench glow
  - resource deduction pulse
  - unlocked tier stamp

Likely files:

- `frontend/src/components/features/UpgradesTab.tsx`
- `frontend/src/components/dashboard/UpgradePreviewPanel.tsx`
- `frontend/src/data/upgrade-copy.ts`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- Upgrades read as power spikes.
- Player can distinguish purchased, available, and locked upgrades immediately.
- Upgrade effects are phrased in gameplay terms.

## Phase 10: Living Background

Problem: the background is stylish but static.

Implementation:

- Add a lightweight environmental effects layer in `ForgeShell`:
  - drifting embers
  - occasional sparks
  - firelight gradient flicker
  - optional mouse parallax on wide screens
- Prefer CSS pseudo-elements and small DOM elements over canvas unless performance requires canvas.
- Pause or simplify effects under reduced-motion.

Likely files:

- `frontend/src/components/layout/ForgeShell.tsx`
- `frontend/src/styles/forge-theme.css`

Acceptance criteria:

- The workshop feels alive before the player clicks anything.
- Effects do not distract from text or controls.
- CPU/GPU cost remains low on the published page.

## Suggested Delivery Order

1. Forge centerpiece and hierarchy pass.
2. Workshop navigation.
3. Resource feedback and environmental effects.
4. Recipe craft loop.
5. Inventory chest.
6. Customer personality.
7. Materials transaction feedback.
8. Upgrade milestone pass.

This order improves the first impression first, then adds deeper loops across the supporting screens.

## Testing And Review Checklist

Run after each implementation batch:

```powershell
cd frontend
npm run type-check
npm run lint
npm run build
```

Before review screenshots:

```powershell
.\publish.ps1
```

Capture fresh screenshots into `review-screenshots/` for:

- Forge dashboard
- Recipes
- Customers
- Materials
- Upgrades

Manual review checks:

- Forge is visually dominant at 1440px desktop and mobile width.
- Primary actions have visible hover, focus, active, success, and failure states.
- Resource changes have feedback.
- Animations respect reduced motion.
- No text overlaps at desktop or mobile widths.
- Screens still work with guest session from a clean browser state.

## Deferred Questions

- Should quick-sell exist as a real gameplay action, or remain out of scope until backend support is added?
- Should market supply/demand become real game state, or stay presentation-only for this pass?
- Should customer flavor lines eventually move into backend data so they can drive future quests/orders?
