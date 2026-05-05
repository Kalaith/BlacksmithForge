UPDATE customer_types
SET icon = '🛡️', description = 'Needs gear that survives another patrol.'
WHERE name = 'Village Guard';

UPDATE customer_types
SET icon = '💰', description = 'Wants a fair deal before the road calls again.'
WHERE name = 'Traveling Merchant';

UPDATE customer_types
SET icon = '👑', description = 'Expects polished steel fit for court.'
WHERE name = 'Noble Knight';

UPDATE customer_types
SET icon = '⚔️', description = 'Needs dependable training gear on a tight purse.'
WHERE name = 'Apprentice Warrior';

UPDATE customer_types
SET icon = '🔨', description = 'Judges every edge, rivet, and temper line.'
WHERE name = 'Master Blacksmith';
