# Blacksmith Forge UI Mockup Implementation Guide

Reference mockup: `ChatGPT Image Apr 26, 2026, 05_11_00 PM.png`

This guide describes how to move the current tabbed UI toward the darker forge-workshop mockup while keeping the backend/API work untouched. Use temporary assets first, then replace them with final generated or commissioned art once the layout is proven.

## Target Direction

The mockup changes the game from a light panel UI into a dashboard inside a blacksmith workshop:

- Full-screen dark forge background with warm fire lighting.
- Metal, stone, and dark wood framed panels.
- A larger first screen that combines navigation, stats, customers, forge, inventory, market, recipes, and upgrades.
- Icon-led controls with short labels.
- Gold/orange highlights for primary actions and teal/green only for auth/session state.
- Dense information cards rather than large empty white cards.

Keep gameplay state and API calls exactly as they are. This is a presentation-layer redesign.

## Temporary Assets

Create temporary assets under:

```text
frontend/public/temp-assets/blacksmith/
```

Use these filenames so components and CSS can be wired once and assets can be swapped later:

```text
forge-workshop-bg.jpg
forge-card.jpg
inventory-chest.png
market-materials.png
recipe-book.png
upgrade-anvil.png
crest-village-guard.png
crest-merchant.png
crest-knight.png
crest-warrior.png
crest-blacksmith.png
panel-texture.png
button-texture.png
```

For the first pass, these can be low-resolution placeholders:

- Use the supplied mockup crop as visual reference only.
- Use generated placeholder images or simple public-domain texture placeholders.
- If no image exists yet, use CSS gradients plus the stable path names above.
- Keep all asset references behind constants so final art replacement is file-only where possible.

Add an asset mapping file:

```text
frontend/src/data/ui-assets.ts
```

Suggested shape:

```ts
export const uiAssets = {
  background: '/blacksmith_forge/temp-assets/blacksmith/forge-workshop-bg.jpg',
  forge: '/blacksmith_forge/temp-assets/blacksmith/forge-card.jpg',
  inventory: '/blacksmith_forge/temp-assets/blacksmith/inventory-chest.png',
  market: '/blacksmith_forge/temp-assets/blacksmith/market-materials.png',
  recipes: '/blacksmith_forge/temp-assets/blacksmith/recipe-book.png',
  upgrades: '/blacksmith_forge/temp-assets/blacksmith/upgrade-anvil.png',
};
```

## Proposed Component Structure

Keep the current pages during migration, but introduce a new dashboard shell first:

```text
src/components/layout/
  ForgeShell.tsx
  ForgeTopBar.tsx
  ForgeNavigation.tsx
  QuickActions.tsx

src/components/dashboard/
  ForgeDashboard.tsx
  CustomerPreviewPanel.tsx
  ForgePreviewPanel.tsx
  InventoryPreviewPanel.tsx
  MarketPreviewPanel.tsx
  RecipePreviewPanel.tsx
  UpgradePreviewPanel.tsx

src/components/ui/
  OrnatePanel.tsx
  FramedButton.tsx
  ResourceStat.tsx
  CrestIcon.tsx
```

Then migrate existing pages to reuse `OrnatePanel`, `FramedButton`, and the new color tokens.

## Layout Plan

### 1. App Shell

Replace the current white header/nav stack with a fixed-width fantasy HUD:

- `ForgeShell` owns the workshop background and global padding.
- `ForgeTopBar` contains title, subtitle, resource stats, and user session block.
- `ForgeNavigation` keeps the current tab behavior, but styles nav as metal tabs.
- `QuickActions` moves trophy/settings/save buttons into circular framed buttons below the user block.

Current files to refactor:

- `frontend/src/App.tsx`
- `frontend/src/components/layout/GameLayout.tsx`
- `frontend/src/components/layout/GameHeader.tsx`
- `frontend/src/components/layout/GameNav.tsx`
- `frontend/src/styles/style.css`

### 2. Dashboard First Screen

The mockup first screen should be the `forge` tab dashboard, not a separate route.

Replace `ForgeTab` content with:

- Left: `CustomerPreviewPanel`
- Center: `ForgePreviewPanel`
- Right: `InventoryPreviewPanel`
- Bottom row: `MarketPreviewPanel`, `RecipePreviewPanel`, `UpgradePreviewPanel`

Each preview panel should link to the matching existing tab by calling `onTabChange`.

This requires passing tab navigation into `ForgePage`/`ForgeTab`, or lifting dashboard rendering into `App.tsx`.

Recommended low-risk approach:

1. Add `onTabChange` prop to `ForgePage`.
2. Add `onTabChange` prop to `ForgeTab`.
3. Keep crafting interactions inside `ForgePreviewPanel`.
4. Use current `CustomersTab`, `MaterialsTab`, `RecipesTab`, and `UpgradesTab` for full-detail pages until they are restyled.

### 3. Panel System

Create one reusable frame component:

```tsx
interface OrnatePanelProps {
  title?: string;
  icon?: React.ReactNode;
  className?: string;
  children: React.ReactNode;
}
```

Visual requirements:

- Dark textured background.
- 1px inner border plus brighter corner accents.
- 6px or less border radius.
- Subtle box-shadow, not floating-card style.
- Gold title text, small uppercase labels.
- No nested cards unless rendering repeated list rows.

Use this component for all major mockup panels.

### 4. CSS Token Layer

Add a mockup-specific theme section near the bottom of `style.css` or split into:

```text
frontend/src/styles/forge-theme.css
```

Import it after existing styles.

Suggested tokens:

```css
:root {
  --forge-bg: #090705;
  --forge-panel: rgba(24, 21, 17, 0.94);
  --forge-panel-2: rgba(37, 31, 24, 0.94);
  --forge-border: #4d3b28;
  --forge-border-bright: #8d6740;
  --forge-gold: #d7a64b;
  --forge-gold-soft: #f0d28a;
  --forge-text: #e8dcc6;
  --forge-muted: #b9a78c;
  --forge-danger: #c84f42;
  --forge-success: #7fb89a;
}
```

Avoid making the whole UI only brown/orange. Use:

- Muted blue for shields/reputation.
- Deep green/teal for guest/auth.
- Steel gray for disabled/inactive controls.
- Red only for missing materials/errors.

### 5. Navigation Styling

Keep current tab keys:

```ts
'forge' | 'recipes' | 'materials' | 'customers' | 'upgrades'
```

Change presentation only:

- Active tab: ember glow and gold text.
- Inactive tabs: charcoal/steel with muted text.
- Use current emoji icons initially.
- Later replace with `lucide-react` icons or raster crest icons.

### 6. Data Mapping

Use existing backend-loaded data:

- Customers preview: first 5 customer types.
- Market preview: first 5 unique materials.
- Recipe preview: first 3 unique recipes.
- Upgrade preview: first 3 unique upgrades.
- Inventory preview: current inventory item count and empty chest illustration.

Do not introduce new frontend-only state. All data should still come through `useGameDataContext`, `useInventory`, and current API services.

## Implementation Phases

### Phase 1: Theme And Assets

1. Add temporary assets under `frontend/public/temp-assets/blacksmith/`.
2. Add `frontend/src/data/ui-assets.ts`.
3. Add `frontend/src/styles/forge-theme.css`.
4. Import the new CSS after existing CSS in `App.tsx`.
5. Restyle only the shell/header/nav/quick actions.

Acceptance checks:

- Existing tabs still switch.
- Guest/session data still displays.
- No header overlap at desktop or mobile.
- Background image works with missing temp assets gracefully.

### Phase 2: Reusable UI Pieces

1. Add `OrnatePanel`.
2. Add `FramedButton`.
3. Add `ResourceStat`.
4. Replace repeated panel wrappers in current pages gradually.

Acceptance checks:

- No change to API calls.
- Cards remain readable at 1440px, 1024px, and 390px widths.
- Text does not overflow buttons or stat boxes.

### Phase 3: Forge Dashboard

1. Add `ForgeDashboard`.
2. Replace the current Forge tab layout with the dashboard layout.
3. Keep the light-forge action in `ForgePreviewPanel`.
4. Add preview buttons: `View All Customers`, `Browse Market`, `View Recipes`, `View Upgrades`, `View Inventory`.

Acceptance checks:

- `Forge` tab matches the mockup composition.
- Buttons switch to existing detail tabs.
- Inventory still reflects backend/user state.
- Browser console has no API errors.

### Phase 4: Detail Page Restyle

Restyle existing full pages after the dashboard works:

- `RecipesTab`: compact recipe ledger cards.
- `MaterialsTab`: market rows/cards with gold prices.
- `CustomersTab`: crest rows like the mockup.
- `UpgradesTab`: upgrade list with anvil/tool art.
- `InventoryPanel`: dark empty chest state and item rows.

Acceptance checks:

- Full pages share the same visual system.
- Lists remain scannable with more than 10 items.
- Missing materials and disabled purchases remain obvious.

### Phase 5: Final Art Pass

Replace temporary assets with final files using the same filenames or update only `ui-assets.ts`.

Final asset requirements:

- `forge-workshop-bg.jpg`: 1920x1080 minimum, darker right side acceptable, readable left panel area.
- Panel art: 800px wide source images, compressed for web.
- Crest icons: transparent PNG or SVG, 128px square source.
- Texture images: subtle enough that text remains readable.

## Responsive Rules

Desktop target:

- Max content width: 1440px.
- Dashboard grid: 2fr 1fr 1fr top content, 1fr 1fr 1fr bottom cards.
- Right-side anvil/background art may remain visible.

Tablet target:

- Two-column dashboard.
- Customer panel can span full width.
- Preview cards stack below forge/inventory.

Mobile target:

- Single-column layout.
- Nav becomes horizontal scroll.
- Top stats become a compact two-row grid.
- Background remains fixed or simplified, but panels must stay readable.
- Do not use viewport-scaled font sizes.

## Testing Checklist

Run after each phase:

```powershell
npm run type-check
npm run lint
npm run build
```

Then publish and capture screenshots:

```powershell
.\publish.ps1
```

Manual smoke:

- Continue as guest.
- Verify header stats and username.
- Visit all five tabs.
- Buy one material if gold is available.
- Open Forge and ensure recipe validation does not throw console errors.
- Confirm screenshots at desktop and mobile widths.

## Risks To Avoid

- Do not reintroduce localStorage-backed gameplay services.
- Do not hardcode development API URLs.
- Do not put large nested cards inside other cards.
- Do not use the mockup image itself as the production background.
- Do not hide backend/API errors behind empty lists.
- Do not let panel textures reduce text contrast.

## First Pull Request Scope

Keep the first PR small:

- Add temp asset paths and theme CSS.
- Add reusable frame/button/stat components.
- Restyle shell/header/nav.
- Implement `ForgeDashboard` using current backend data.
- Leave detail tab restyling for a second PR.

