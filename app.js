// Game State Management
class BlacksmithGame {
    constructor() {
        this.gameState = {
            player: {
                gold: 100,
                reputation: 0,
                level: 1,
                experience: 0
            },
            inventory: [],
            unlockedRecipes: ['Iron Dagger', 'Iron Sword'],
            materials: {
                'Iron Ore': 0,
                'Coal': 0,
                'Wood': 0,
                'Leather': 0,
                'Silver Ore': 0,
                'Mythril': 0
            },
            forgeUpgrades: [],
            forgeLit: false,
            currentCustomer: null,
            tutorialCompleted: false,
            tutorialStep: 0
        };

        this.gameData = {
            materials: [
                {"name": "Iron Ore", "cost": 5, "quality": "common", "description": "Basic material for most weapons", "icon": "⛏️"},
                {"name": "Coal", "cost": 2, "quality": "common", "description": "Fuel for the forge", "icon": "🔥"},
                {"name": "Wood", "cost": 3, "quality": "common", "description": "For handles and hilts", "icon": "🌳"},
                {"name": "Leather", "cost": 4, "quality": "common", "description": "For grips and armor padding", "icon": "🐄"},
                {"name": "Silver Ore", "cost": 25, "quality": "rare", "description": "Precious metal for decorative elements", "icon": "✨"},
                {"name": "Mythril", "cost": 100, "quality": "legendary", "description": "Rare magical metal", "icon": "💎"}
            ],
            recipes: [
                {"name": "Iron Dagger", "materials": {"Iron Ore": 1, "Wood": 1, "Coal": 1}, "sellPrice": 15, "difficulty": 1, "icon": "🗡️"},
                {"name": "Iron Sword", "materials": {"Iron Ore": 2, "Wood": 1, "Leather": 1, "Coal": 2}, "sellPrice": 35, "difficulty": 2, "icon": "⚔️"},
                {"name": "Steel Axe", "materials": {"Iron Ore": 3, "Wood": 2, "Coal": 3}, "sellPrice": 45, "difficulty": 3, "icon": "🪓"},
                {"name": "Silver Blade", "materials": {"Silver Ore": 1, "Iron Ore": 1, "Wood": 1, "Coal": 2}, "sellPrice": 80, "difficulty": 4, "icon": "🌟"}
            ],
            customers: [
                {"name": "Village Guard", "budget": 50, "preferences": "durability", "reputation": 0, "icon": "🛡️"},
                {"name": "Traveling Merchant", "budget": 30, "preferences": "value", "reputation": 0, "icon": "🎒"},
                {"name": "Noble Knight", "budget": 150, "preferences": "quality", "reputation": 0, "icon": "👑"},
                {"name": "Young Adventurer", "budget": 25, "preferences": "balanced", "reputation": 0, "icon": "🗡️"}
            ],
            forgeUpgrades: [
                {"name": "Better Bellows", "cost": 100, "effect": "Reduces coal consumption", "icon": "💨"},
                {"name": "Precision Anvil", "cost": 200, "effect": "Improves crafting accuracy", "icon": "🔨"},
                {"name": "Master Tools", "cost": 500, "effect": "Unlocks advanced recipes", "icon": "⚒️"}
            ]
        };

        this.selectedRecipe = null;
        this.craftingInProgress = false;
        this.hammerAccuracy = 0;
        this.hammerClicks = 0;
        this.maxHammerClicks = 4;
        this.tutorialSteps = [
            "Welcome to your forge! You'll craft weapons and armor to sell to customers.",
            "First, click on the forge fire to light it. You'll need this for crafting.",
            "Check your Materials tab to buy raw materials like Iron Ore, Coal, and Wood.",
            "Visit the Recipes tab to see what you can craft and what materials you need.",
            "Return to the Forge to start crafting your first weapon!",
            "Sell your crafted items to customers in the Customers tab to earn gold.",
            "Use your profits to buy better materials and upgrade your forge!"
        ];

        this.init();
    }

    init() {
        this.loadGameState();
        this.setupEventListeners();
        this.updateUI();
        
        if (!this.gameState.tutorialCompleted) {
            this.showTutorial();
        }

        // Generate initial customer
        this.generateNewCustomer();
        
        // Auto-save every 30 seconds
        setInterval(() => this.saveGameState(), 30000);
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });

        // Forge fire
        document.getElementById('forge-fire').addEventListener('click', () => {
            this.lightForge();
        });

        // Tutorial
        document.getElementById('tutorial-next').addEventListener('click', () => {
            this.nextTutorialStep();
        });

        document.getElementById('tutorial-skip').addEventListener('click', () => {
            this.skipTutorial();
        });

        // Crafting
        document.getElementById('start-crafting').addEventListener('click', () => {
            this.startCrafting();
        });

        document.getElementById('cancel-crafting').addEventListener('click', () => {
            this.cancelCrafting();
        });

        document.getElementById('hammer-btn').addEventListener('click', () => {
            this.hammer();
        });

        // Modal closes
        document.getElementById('close-result').addEventListener('click', () => {
            document.getElementById('crafting-result-modal').classList.add('hidden');
        });

        document.getElementById('close-sale').addEventListener('click', () => {
            document.getElementById('sale-modal').classList.add('hidden');
        });
    }

    switchTab(tabName) {
        // Update nav buttons
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');

        // Update tab-specific content
        switch(tabName) {
            case 'recipes':
                this.updateRecipesDisplay();
                break;
            case 'materials':
                this.updateMaterialsDisplay();
                break;
            case 'customers':
                this.updateCustomersDisplay();
                break;
            case 'upgrades':
                this.updateUpgradesDisplay();
                break;
            case 'forge':
                this.updateForgeDisplay();
                break;
        }
    }

    lightForge() {
        if (!this.gameState.forgeLit) {
            this.gameState.forgeLit = true;
            const forgeElement = document.getElementById('forge-fire');
            forgeElement.classList.add('lit');
            forgeElement.innerHTML = '<div class="fire-animation">🔥🔥🔥</div><p>Forge is burning hot!</p>';
            
            document.getElementById('crafting-area').classList.remove('hidden');
            this.updateRecipeSelector();
        }
    }

    updateRecipeSelector() {
        const container = document.getElementById('recipe-selector');
        container.innerHTML = '';

        this.gameState.unlockedRecipes.forEach(recipeName => {
            const recipe = this.gameData.recipes.find(r => r.name === recipeName);
            if (!recipe) return;

            const recipeCard = document.createElement('div');
            recipeCard.className = 'recipe-card';
            recipeCard.dataset.recipe = recipe.name;

            const canCraft = this.canCraftRecipe(recipe);
            const profitMargin = this.calculateProfit(recipe);

            recipeCard.innerHTML = `
                <div class="recipe-name">${recipe.icon} ${recipe.name}</div>
                <div class="recipe-difficulty difficulty-${recipe.difficulty}">
                    Difficulty: ${'★'.repeat(recipe.difficulty)}
                </div>
                <div class="recipe-profit">Profit: ${profitMargin}g</div>
                ${!canCraft ? '<div style="color: var(--color-error); font-size: var(--font-size-xs);">Missing materials</div>' : ''}
            `;

            if (!canCraft) {
                recipeCard.style.opacity = '0.5';
            }

            recipeCard.addEventListener('click', () => {
                this.selectRecipe(recipe);
            });

            container.appendChild(recipeCard);
        });
    }

    selectRecipe(recipe) {
        this.selectedRecipe = recipe;
        
        // Update visual selection
        document.querySelectorAll('.recipe-card').forEach(card => {
            card.classList.remove('selected');
        });
        document.querySelector(`[data-recipe="${recipe.name}"]`).classList.add('selected');

        // Show crafting interface
        document.getElementById('crafting-interface').classList.remove('hidden');
        document.getElementById('selected-recipe-name').textContent = `${recipe.icon} ${recipe.name}`;

        // Show required materials
        const materialsContainer = document.getElementById('required-materials');
        materialsContainer.innerHTML = '';

        let totalCost = 0;
        let canCraftThis = true;
        
        Object.entries(recipe.materials).forEach(([materialName, quantity]) => {
            const material = this.gameData.materials.find(m => m.name === materialName);
            const hasEnough = this.gameState.materials[materialName] >= quantity;
            if (!hasEnough) canCraftThis = false;
            totalCost += material.cost * quantity;

            const materialReq = document.createElement('div');
            materialReq.className = `material-requirement ${!hasEnough ? 'insufficient' : ''}`;
            materialReq.innerHTML = `${material.icon} ${materialName}: ${quantity} (Have: ${this.gameState.materials[materialName]})`;
            materialsContainer.appendChild(materialReq);
        });

        document.getElementById('crafting-cost').textContent = totalCost;
        
        // Enable/disable start crafting button based on materials
        const startButton = document.getElementById('start-crafting');
        startButton.disabled = !canCraftThis;
        startButton.style.display = 'inline-flex'; // Ensure button is always visible
        
        if (!canCraftThis) {
            startButton.textContent = 'Insufficient Materials';
        } else {
            startButton.textContent = 'Start Crafting';
        }
    }

    canCraftRecipe(recipe) {
        return Object.entries(recipe.materials).every(([materialName, quantity]) => {
            return this.gameState.materials[materialName] >= quantity;
        });
    }

    calculateProfit(recipe) {
        const cost = Object.entries(recipe.materials).reduce((sum, [materialName, quantity]) => {
            const material = this.gameData.materials.find(m => m.name === materialName);
            return sum + (material.cost * quantity);
        }, 0);
        return recipe.sellPrice - cost;
    }

    startCrafting() {
        if (!this.selectedRecipe || this.craftingInProgress) return;

        // Check materials again
        if (!this.canCraftRecipe(this.selectedRecipe)) {
            alert('You don\'t have enough materials!');
            return;
        }

        // Consume materials
        Object.entries(this.selectedRecipe.materials).forEach(([materialName, quantity]) => {
            this.gameState.materials[materialName] -= quantity;
        });

        this.craftingInProgress = true;
        this.hammerAccuracy = 0;
        this.hammerClicks = 0;
        
        // Show mini-game
        document.getElementById('hammering-game').classList.remove('hidden');
        document.getElementById('start-crafting').disabled = true;
        
        // Reset hammer game display
        document.getElementById('hammer-accuracy').textContent = '0';
        
        // Update instructions
        const hammerGame = document.getElementById('hammering-game');
        hammerGame.querySelector('h4').textContent = `Perfect the Timing! (${this.hammerClicks}/${this.maxHammerClicks} hits)`;
    }

    hammer() {
        if (!this.craftingInProgress) return;
        
        const cursor = document.getElementById('hammer-cursor');
        const target = document.getElementById('hammer-target');
        const hammerBar = document.querySelector('.hammer-bar');
        
        // Get positions relative to the hammer bar
        const barRect = hammerBar.getBoundingClientRect();
        const cursorRect = cursor.getBoundingClientRect();
        const targetRect = target.getBoundingClientRect();
        
        // Calculate cursor position within the bar (0 to 1)
        const cursorPosition = (cursorRect.left - barRect.left) / barRect.width;
        const targetStart = (targetRect.left - barRect.left) / barRect.width;
        const targetEnd = (targetRect.right - barRect.left) / barRect.width;
        
        // Check if cursor is in target zone
        if (cursorPosition >= targetStart && cursorPosition <= targetEnd) {
            this.hammerAccuracy += 25;
            // Visual feedback for successful hit
            target.style.backgroundColor = 'var(--color-success)';
            setTimeout(() => {
                target.style.backgroundColor = 'var(--color-success)';
            }, 200);
        } else {
            this.hammerAccuracy = Math.max(0, this.hammerAccuracy - 5);
            // Visual feedback for missed hit
            hammerBar.classList.add('shake');
            setTimeout(() => {
                hammerBar.classList.remove('shake');
            }, 500);
        }
        
        this.hammerClicks++;
        this.hammerAccuracy = Math.min(100, this.hammerAccuracy);
        
        // Update display
        document.getElementById('hammer-accuracy').textContent = this.hammerAccuracy;
        const hammerGame = document.getElementById('hammering-game');
        hammerGame.querySelector('h4').textContent = `Perfect the Timing! (${this.hammerClicks}/${this.maxHammerClicks} hits)`;
        
        // Check if crafting is complete
        if (this.hammerClicks >= this.maxHammerClicks) {
            let quality = 'poor';
            if (this.hammerAccuracy >= 80) {
                quality = 'excellent';
            } else if (this.hammerAccuracy >= 60) {
                quality = 'good';
            } else if (this.hammerAccuracy >= 40) {
                quality = 'fair';
            }
            
            setTimeout(() => {
                this.completeCrafting(quality);
            }, 1000);
        }
    }

    completeCrafting(quality) {
        this.craftingInProgress = false;
        
        // Hide mini-game
        document.getElementById('hammering-game').classList.add('hidden');
        document.getElementById('start-crafting').disabled = false;
        
        // Calculate final item stats
        const basePrice = this.selectedRecipe.sellPrice;
        let qualityMultiplier = 1;
        let qualityText = '';
        
        switch(quality) {
            case 'excellent':
                qualityMultiplier = 1.5;
                qualityText = 'Excellent';
                break;
            case 'good':
                qualityMultiplier = 1.2;
                qualityText = 'Good';
                break;
            case 'fair':
                qualityMultiplier = 1;
                qualityText = 'Fair';
                break;
            case 'poor':
                qualityMultiplier = 0.8;
                qualityText = 'Poor';
                break;
        }
        
        const finalPrice = Math.floor(basePrice * qualityMultiplier);
        
        // Add to inventory
        const item = {
            name: this.selectedRecipe.name,
            icon: this.selectedRecipe.icon,
            quality: qualityText,
            value: finalPrice,
            type: 'weapon'
        };
        
        this.gameState.inventory.push(item);
        
        // Add experience
        this.gameState.player.experience += this.selectedRecipe.difficulty * 10;
        this.checkLevelUp();
        
        // Show result
        document.getElementById('result-content').innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: var(--font-size-4xl); margin-bottom: var(--space-16);">${item.icon}</div>
                <h3>${item.name}</h3>
                <div class="status status--success">${qualityText} Quality</div>
                <p style="margin-top: var(--space-16);">Final Value: ${finalPrice} gold</p>
                <p>+${this.selectedRecipe.difficulty * 10} Experience</p>
                <p style="font-size: var(--font-size-sm); color: var(--color-text-secondary);">
                    Crafting Accuracy: ${this.hammerAccuracy}%
                </p>
            </div>
        `;
        
        document.getElementById('crafting-result-modal').classList.remove('hidden');
        
        this.updateUI();
        this.cancelCrafting();
    }

    cancelCrafting() {
        this.selectedRecipe = null;
        this.craftingInProgress = false;
        document.getElementById('crafting-interface').classList.add('hidden');
        document.getElementById('hammering-game').classList.add('hidden');
        document.querySelectorAll('.recipe-card').forEach(card => {
            card.classList.remove('selected');
        });
    }

    checkLevelUp() {
        const newLevel = Math.floor(this.gameState.player.experience / 100) + 1;
        if (newLevel > this.gameState.player.level) {
            this.gameState.player.level = newLevel;
            
            // Unlock new recipes
            if (newLevel === 2 && !this.gameState.unlockedRecipes.includes('Steel Axe')) {
                this.gameState.unlockedRecipes.push('Steel Axe');
            }
            if (newLevel === 3 && !this.gameState.unlockedRecipes.includes('Silver Blade')) {
                this.gameState.unlockedRecipes.push('Silver Blade');
            }
        }
    }

    updateMaterialsDisplay() {
        const container = document.getElementById('materials-grid');
        container.innerHTML = '';

        this.gameData.materials.forEach(material => {
            const materialCard = document.createElement('div');
            materialCard.className = 'material-card';

            materialCard.innerHTML = `
                <div class="material-info">
                    <div class="material-name">${material.icon} ${material.name}</div>
                    <div class="material-cost">${material.cost}g</div>
                </div>
                <div class="material-description">${material.description}</div>
                <div class="quality-badge quality-${material.quality}">${material.quality}</div>
                <div style="margin-top: var(--space-12); display: flex; justify-content: space-between; align-items: center;">
                    <span>Owned: ${this.gameState.materials[material.name]}</span>
                    <div>
                        <button class="btn btn--sm btn--secondary" onclick="game.buyMaterial('${material.name}', 1)" 
                                ${this.gameState.player.gold < material.cost ? 'disabled' : ''}>Buy 1</button>
                        <button class="btn btn--sm btn--primary" onclick="game.buyMaterial('${material.name}', 5)"
                                ${this.gameState.player.gold < material.cost * 5 ? 'disabled' : ''}>Buy 5</button>
                    </div>
                </div>
            `;

            container.appendChild(materialCard);
        });
    }

    buyMaterial(materialName, quantity) {
        const material = this.gameData.materials.find(m => m.name === materialName);
        const totalCost = material.cost * quantity;

        if (this.gameState.player.gold >= totalCost) {
            this.gameState.player.gold -= totalCost;
            this.gameState.materials[materialName] += quantity;
            this.updateUI();
            this.updateMaterialsDisplay();
            
            // Update recipe selector if on forge tab
            if (document.getElementById('forge-tab').classList.contains('active')) {
                this.updateRecipeSelector();
            }
        } else {
            alert('Not enough gold!');
        }
    }

    updateRecipesDisplay() {
        const container = document.getElementById('recipes-grid');
        container.innerHTML = '';

        this.gameData.recipes.forEach(recipe => {
            const isUnlocked = this.gameState.unlockedRecipes.includes(recipe.name);
            
            const recipeCard = document.createElement('div');
            recipeCard.className = `recipe-card ${!isUnlocked ? 'locked' : ''}`;
            
            if (!isUnlocked) {
                recipeCard.style.opacity = '0.5';
                recipeCard.innerHTML = `
                    <div class="recipe-name">🔒 ${recipe.name}</div>
                    <div class="recipe-difficulty difficulty-${recipe.difficulty}">
                        Difficulty: ${'★'.repeat(recipe.difficulty)}
                    </div>
                    <p style="color: var(--color-text-secondary); font-size: var(--font-size-sm);">Unlock at level ${recipe.difficulty}</p>
                `;
            } else {
                const materialsText = Object.entries(recipe.materials)
                    .map(([name, qty]) => `${qty}x ${name}`)
                    .join(', ');

                recipeCard.innerHTML = `
                    <div class="recipe-name">${recipe.icon} ${recipe.name}</div>
                    <div class="recipe-difficulty difficulty-${recipe.difficulty}">
                        Difficulty: ${'★'.repeat(recipe.difficulty)}
                    </div>
                    <div style="margin: var(--space-8) 0; font-size: var(--font-size-sm); color: var(--color-text-secondary);">
                        Materials: ${materialsText}
                    </div>
                    <div class="recipe-profit">Sell Price: ${recipe.sellPrice}g</div>
                `;
            }

            container.appendChild(recipeCard);
        });
    }

    generateNewCustomer() {
        const availableCustomers = this.gameData.customers.filter(c => 
            !this.gameState.currentCustomer || c.name !== this.gameState.currentCustomer.name
        );
        
        if (availableCustomers.length > 0) {
            const randomCustomer = availableCustomers[Math.floor(Math.random() * availableCustomers.length)];
            this.gameState.currentCustomer = { ...randomCustomer };
            
            // Random budget variation
            this.gameState.currentCustomer.budget += Math.floor(Math.random() * 20) - 10;
            this.gameState.currentCustomer.budget = Math.max(10, this.gameState.currentCustomer.budget);
        }
    }

    updateCustomersDisplay() {
        const activeContainer = document.getElementById('active-customer');
        
        if (this.gameState.currentCustomer) {
            const customer = this.gameState.currentCustomer;
            activeContainer.innerHTML = `
                <div class="customer-card">
                    <div class="customer-info">
                        <div class="customer-name">${customer.icon} ${customer.name}</div>
                        <div class="customer-budget">Budget: ${customer.budget}g</div>
                    </div>
                    <div class="customer-preferences">
                        Looking for: ${customer.preferences} weapons/armor
                    </div>
                    <div style="margin-top: var(--space-16);">
                        <h4>Your Inventory:</h4>
                        <div class="inventory-grid" id="customer-inventory"></div>
                    </div>
                </div>
            `;
            
            this.updateCustomerInventory();
        } else {
            activeContainer.innerHTML = '<p>No customers waiting. Check back later!</p>';
        }
    }

    updateCustomerInventory() {
        const container = document.getElementById('customer-inventory');
        if (!container) return;
        
        container.innerHTML = '';
        
        if (this.gameState.inventory.length === 0) {
            container.innerHTML = '<p>No items to sell. Craft something first!</p>';
            return;
        }
        
        this.gameState.inventory.forEach((item, index) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'inventory-item';
            itemDiv.innerHTML = `
                <div class="item-icon">${item.icon}</div>
                <div class="item-name">${item.name}</div>
                <div class="item-quantity">${item.quality} Quality</div>
                <div class="item-value">${item.value}g</div>
                <button class="btn btn--sm btn--primary" style="margin-top: var(--space-8);" 
                        onclick="game.sellItem(${index})">Sell</button>
            `;
            container.appendChild(itemDiv);
        });
    }

    sellItem(itemIndex) {
        const item = this.gameState.inventory[itemIndex];
        const customer = this.gameState.currentCustomer;
        
        if (!item || !customer) return;
        
        let finalPrice = item.value;
        
        // Apply customer preference modifier
        if (customer.preferences === 'quality' && item.quality === 'Excellent') {
            finalPrice = Math.floor(finalPrice * 1.2);
        } else if (customer.preferences === 'value' && finalPrice <= customer.budget * 0.7) {
            finalPrice = Math.floor(finalPrice * 1.1);
        }
        
        if (finalPrice > customer.budget) {
            alert(`${customer.name} can't afford this item! (Budget: ${customer.budget}g, Price: ${finalPrice}g)`);
            return;
        }
        
        // Complete sale
        this.gameState.player.gold += finalPrice;
        this.gameState.player.reputation += 1;
        this.gameState.inventory.splice(itemIndex, 1);
        
        // Show sale result
        document.getElementById('sale-content').innerHTML = `
            <div style="text-align: center;">
                <h3>Sale Complete!</h3>
                <p>${customer.name} bought your ${item.name} for ${finalPrice} gold!</p>
                <div class="status status--success">+${finalPrice} Gold</div>
                <div class="status status--info">+1 Reputation</div>
            </div>
        `;
        document.getElementById('sale-modal').classList.remove('hidden');
        
        // Generate new customer after a delay
        setTimeout(() => {
            this.generateNewCustomer();
            this.updateUI();
        }, 2000);
        
        this.updateUI();
        this.updateCustomersDisplay();
    }

    updateUpgradesDisplay() {
        const container = document.getElementById('upgrades-grid');
        container.innerHTML = '';

        this.gameData.forgeUpgrades.forEach(upgrade => {
            const isOwned = this.gameState.forgeUpgrades.includes(upgrade.name);
            
            const upgradeCard = document.createElement('div');
            upgradeCard.className = 'upgrade-card';
            
            upgradeCard.innerHTML = `
                <div class="material-info">
                    <div class="material-name">${upgrade.icon} ${upgrade.name}</div>
                    <div class="material-cost">${isOwned ? 'OWNED' : upgrade.cost + 'g'}</div>
                </div>
                <div class="material-description">${upgrade.effect}</div>
                ${!isOwned ? `<button class="btn btn--primary" style="margin-top: var(--space-12);" 
                    onclick="game.buyUpgrade('${upgrade.name}')" ${this.gameState.player.gold < upgrade.cost ? 'disabled' : ''}>
                    Buy Upgrade
                </button>` : '<div class="status status--success" style="margin-top: var(--space-12);">Owned</div>'}
            `;

            if (isOwned) {
                upgradeCard.style.opacity = '0.7';
            }

            container.appendChild(upgradeCard);
        });
    }

    buyUpgrade(upgradeName) {
        const upgrade = this.gameData.forgeUpgrades.find(u => u.name === upgradeName);
        
        if (this.gameState.player.gold >= upgrade.cost && !this.gameState.forgeUpgrades.includes(upgradeName)) {
            this.gameState.player.gold -= upgrade.cost;
            this.gameState.forgeUpgrades.push(upgradeName);
            
            // Apply upgrade effects
            if (upgradeName === 'Master Tools') {
                // Unlock all recipes
                this.gameData.recipes.forEach(recipe => {
                    if (!this.gameState.unlockedRecipes.includes(recipe.name)) {
                        this.gameState.unlockedRecipes.push(recipe.name);
                    }
                });
            }
            
            this.updateUI();
            this.updateUpgradesDisplay();
        }
    }

    updateForgeDisplay() {
        this.updateRecipeSelector();
        this.updateInventoryDisplay();
    }

    updateInventoryDisplay() {
        const container = document.getElementById('inventory-grid');
        container.innerHTML = '';

        if (this.gameState.inventory.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: var(--color-text-secondary);">No crafted items</p>';
            return;
        }

        this.gameState.inventory.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'inventory-item';
            itemDiv.innerHTML = `
                <div class="item-icon">${item.icon}</div>
                <div class="item-name">${item.name}</div>
                <div class="item-quantity">${item.quality}</div>
                <div class="item-value">${item.value}g</div>
            `;
            container.appendChild(itemDiv);
        });
    }

    updateUI() {
        // Update player stats
        document.getElementById('player-gold').textContent = this.gameState.player.gold;
        document.getElementById('player-reputation').textContent = this.gameState.player.reputation;
        document.getElementById('player-level').textContent = this.gameState.player.level;
        
        // Save game state
        this.saveGameState();
    }

    // Tutorial System
    showTutorial() {
        document.getElementById('tutorial-modal').classList.remove('hidden');
        this.updateTutorialContent();
    }

    updateTutorialContent() {
        const content = document.getElementById('tutorial-content');
        const step = this.gameState.tutorialStep;
        
        content.innerHTML = `
            <p><strong>Step ${step + 1} of ${this.tutorialSteps.length}</strong></p>
            <p>${this.tutorialSteps[step]}</p>
        `;
        
        if (step === this.tutorialSteps.length - 1) {
            document.getElementById('tutorial-next').textContent = 'Finish';
        }
    }

    nextTutorialStep() {
        this.gameState.tutorialStep++;
        
        if (this.gameState.tutorialStep >= this.tutorialSteps.length) {
            this.completeTutorial();
        } else {
            this.updateTutorialContent();
        }
    }

    skipTutorial() {
        this.completeTutorial();
    }

    completeTutorial() {
        this.gameState.tutorialCompleted = true;
        document.getElementById('tutorial-modal').classList.add('hidden');
        
        // Give starting materials
        this.gameState.materials['Iron Ore'] = 5;
        this.gameState.materials['Coal'] = 10;
        this.gameState.materials['Wood'] = 5;
        this.gameState.materials['Leather'] = 3;
        
        this.updateUI();
    }

    // Save/Load System
    saveGameState() {
        try {
            localStorage.setItem('blacksmith-game-save', JSON.stringify(this.gameState));
        } catch (e) {
            console.warn('Could not save game state:', e);
        }
    }

    loadGameState() {
        try {
            const saved = localStorage.getItem('blacksmith-game-save');
            if (saved) {
                const loadedState = JSON.parse(saved);
                this.gameState = { ...this.gameState, ...loadedState };
            }
        } catch (e) {
            console.warn('Could not load game state:', e);
        }
    }
}

// Initialize game when page loads
let game;
document.addEventListener('DOMContentLoaded', () => {
    game = new BlacksmithGame();
});