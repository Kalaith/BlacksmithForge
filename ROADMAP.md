# Blacksmith Forge Roadmap

## Top 3 Priorities
1. **Complete Crafting System Implementation**
   - Implement the full weapon and armor crafting mechanics
   - Add material quality system affecting final product stats
   - Create recipe unlocking progression through experimentation
2. **Customer & Economy System**
   - Add NPC customers with varying needs and budget constraints
   - Implement dynamic pricing based on supply, demand, and reputation
   - Create customer relationship system with repeat business mechanics
3. **Workshop Management Features**
   - Add forge upgrades and tool improvements
   - Implement apprentice hiring and training systems
   - Create bulk production and order fulfillment mechanics

## Crafting System Enhancement
1. **Recipe Discovery System**: Unlock new recipes through experimentation, research, or finding ancient blueprints
2. **Quality Variations**: Implement weapon quality tiers (Poor, Common, Rare, Epic, Legendary) with different stats
3. **Enchantment System**: Add magical enchantments with special effects and rune combinations
4. **Custom Weapon Designer**: Visual weapon customization with component selection and aesthetic options
5. **Material Properties**: Different materials affect weapon stats, durability, and special properties

## Business Simulation
6. **Market Dynamics**: Fluctuating material prices and weapon demand based on world events
7. **Reputation System**: Build reputation with different customer types affecting prices and special orders
8. **Competitor AI**: Rival blacksmiths that affect market prices and compete for customers
9. **Guild Contracts**: Large orders from adventuring guilds and armies with deadline pressure
10. **Shop Customization**: Upgrade and customize the forge layout, tools, and decorations

## Advanced Gameplay
11. **Apprentice System**: Hire and train apprentices to automate basic tasks and expand production
12. **Research & Development**: Invest in research to unlock new technologies and crafting techniques
13. **World Events**: Random events affecting supply, demand, and available materials
14. **Customer Relationships**: Build relationships with recurring customers for better deals and exclusive orders
15. **Seasonal Crafting**: Seasonal materials and limited-time recipes tied to in-game calendar

## Technical Improvements
18. **Tutorial System**: Comprehensive onboarding with guided crafting lessons
19. **Achievement System**: Unlock achievements for crafting milestones, rare items, and business success
20. **Export/Import**: Export custom recipes and weapon designs as JSON files for backup and sharing

## Implementation Phases (Top 5 Features)
### Phase 1: Core Crafting Loop (MVP)
- **User stories**
- As a player, I can select a recipe and craft an item to see its stats.
- As a player, I can see required materials and whether I have enough.
- As a player, I can view a craft result preview before confirming.
- **Acceptance criteria**
- Crafting produces a valid item with stats every time.
- Materials are consumed on successful craft, not on cancel.
- UI shows recipe list, required materials, and output stats.
- Crafted items are saved and visible in inventory.

### Phase 2: Material Quality + Quality Tiers
- **User stories**
- As a player, I can use different material grades to affect results.
- As a player, I can see the item’s quality tier and stat bonuses.
- As a player, I can understand why a quality tier was achieved.
- **Acceptance criteria**
- Materials have quality and property modifiers.
- Output item quality tier is derived from inputs and process rules.
- UI displays quality tier and its stat deltas.
- Quality tiers map to named levels (Poor, Common, Rare, Epic, Legendary).

### Phase 3: Recipe Discovery
- **User stories**
- As a player, I can discover new recipes via experimentation.
- As a player, I can track discovered recipes in a codex.
- As a player, I can fail to discover recipes if conditions aren’t met.
- **Acceptance criteria**
- Experimentation has deterministic rules and a success threshold.
- Newly discovered recipes are persisted.
- UI distinguishes known vs unknown recipes.
- Discovery events are logged with short feedback text.

### Phase 4: Customer & Economy Foundations
- **User stories**
- As a player, I can receive customer requests with budgets.
- As a player, I can price items and accept or reject offers.
- As a player, I can see demand trends by item type.
- **Acceptance criteria**
- Customers have needs, budgets, and item preferences.
- A baseline pricing model exists (cost + margin).
- Demand tracking influences which requests appear.
- Orders can be completed and paid out.

### Phase 5: Customer Relationships + Dynamic Pricing
- **User stories**
- As a player, I can build reputation with customers.
- As a player, I can earn better orders and prices through reputation.
- As a player, I can react to price shifts from supply/demand changes.
- **Acceptance criteria**
- Reputation is tracked per customer type or faction.
- Reputation modifies available orders and pricing.
- Prices shift over time based on supply/demand signals.
- UI surfaces current reputation tier and price multipliers.

### Phase 6: Workshop Management
- **User stories**
- As a player, I can upgrade my forge and tools.
- As a player, I can hire and train apprentices for basic tasks.
- As a player, I can accept bulk orders and fulfill them over time.
- **Acceptance criteria**
- Upgrades apply measurable effects (speed, quality, capacity).
- Apprentices can be assigned to at least one automated task.
- Bulk orders track progress and deadlines.
- Workshop upgrades and staff are persisted.
