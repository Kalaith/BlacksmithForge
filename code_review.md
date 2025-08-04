Avoid Magic Numbers in Game Logic

Why: Magic numbers (e.g., MAX_HAMMER_CLICKS = 4, accuracy thresholds) reduce readability and maintainability.
How: Move all such values to a centralized constants/gameConfig.ts file and use named exports (e.g., MAX_HAMMER_CLICKS, HAMMER_SUCCESS_THRESHOLD).
Explicit TypeScript Typing Everywhere

Why: Implicit any types (e.g., in inventory mapping, event handlers) can lead to runtime errors and poor IntelliSense.
How: Define and use interfaces for all props, state, and mapped objects. For example, inventory.map((item: InventoryItem, idx: number) => ...).
Centralize Game Data and Config

Why: Scattered config/data (materials, recipes, thresholds) makes updates error-prone.
How: Use a single constants/gameData.ts and constants/gameConfig.ts for all static data and config values.
Separate Business Logic from UI Components

Why: Mixing state updates and game rules inside components (e.g., handleSell, handleBuyUpgrade) makes testing and reuse difficult.
How: Move business logic to custom hooks (e.g., useGameActions) and keep components focused on rendering.
Consistent Naming Conventions

Why: Inconsistent names (e.g., handleBuy, handleBuyUpgrade, handleSell) can confuse developers.
How: Use clear, action-oriented names like buyMaterial, sellInventoryItem, purchaseUpgrade.
Use Enums for Game States and Qualities

Why: String literals for qualities ('Excellent', 'Good', etc.) and tab keys are error-prone.
How: Define enums (e.g., enum ItemQuality { Excellent, Good, Fair, Poor }) and use them throughout.
Propagate Proper Typing in Context and Hooks

Why: Context values and setters should be strictly typed for safety.
How: Annotate context values and setters, e.g., setState: React.Dispatch<React.SetStateAction<GameState>>.
Avoid Inline Styles and Use CSS Classes

Why: Inline styles (e.g., style={{ marginTop: '12px' }}) are harder to maintain and override.
How: Move styles to CSS classes in your stylesheet and use class names in JSX.
Componentization of Repeated UI Patterns

Why: Repeated markup (e.g., inventory items, material cards) increases code duplication.
How: Extract reusable components like <InventoryItem />, <MaterialCard />, <UpgradeCard />.
Error Handling and User Feedback

Why: Silent failures (e.g., buy/sell actions with insufficient gold) degrade UX.
How: Add error states and feedback (e.g., toast notifications, disabled buttons with tooltips explaining why).