# Ashes of Aeloria - Clean Architecture Implementation

## Overview

This document describes the clean, modular architecture implementation for Ashes of Aeloria frontend, following the established frontend standards. The refactoring focuses on maintainability, type safety, reusability, and separation of concerns.

## 🏗️ Architecture Overview

### Core Principles Applied

- ✅ **Single Responsibility Principle**: Each component/function has one clear purpose
- ✅ **Type Safety**: Full TypeScript coverage with comprehensive interfaces  
- ✅ **Reusable Components**: Generic, composable UI components
- ✅ **Separation of Concerns**: Clear boundaries between UI, business logic, and data
- ✅ **Modern React Patterns**: Hooks, functional components, clean state management
- ✅ **No Prop Drilling**: Custom hooks for clean data flow

## 📁 New Directory Structure

```
src/
├── components/
│   ├── ui/                    # Pure, reusable UI components
│   │   ├── EnhancedButton.tsx # Enhanced button with variants & loading states
│   │   ├── Toast.tsx          # Notification system components
│   │   ├── Card.tsx           # (existing)
│   │   ├── Modal.tsx          # (existing)
│   │   └── index.enhanced.ts  # Clean export interface
│   ├── features/              # Feature-specific business components
│   │   ├── ResourceDisplay.tsx       # Game resource information
│   │   ├── GameStatus.tsx            # Turn/phase status display
│   │   ├── NodeInfo.tsx              # Node details and actions
│   │   ├── CommanderInfo.tsx         # Commander details and stats
│   │   ├── EnhancedLeftPanel.tsx     # Modular left panel
│   │   ├── EnhancedRightPanel.tsx    # Modular right panel
│   │   ├── EnhancedInfoPanel.tsx     # Enhanced information display
│   │   ├── EnhancedCommandersPanel.tsx # Self-contained commanders management
│   │   ├── GameOverModal.tsx         # Victory/defeat modal
│   │   └── index.ts                  # Clean export interface
│   ├── layout/                # Layout and structural components
│   │   ├── GameLayout.tsx     # Responsive layout container
│   │   ├── GameHeader.tsx     # (existing)
│   │   └── (other existing)
│   └── game/                  # Game-specific components
│       └── (existing components)
├── hooks/                     # Custom hooks for business logic
│   ├── useGameActions.ts      # Centralized game actions & validation
│   ├── useGameLogic.ts        # (existing) Game logic hook
│   ├── useNotifications.ts    # Toast notification management
│   ├── useModals.ts           # Modal state management
│   └── index.ts               # Clean export interface
├── providers/                 # Context providers
│   └── GameProvider.tsx       # Global game context
├── constants/                 # Centralized configuration
│   └── index.ts               # All constants and configuration
├── stores/                    # State management (existing)
├── types/                     # TypeScript definitions (existing) 
├── utils/                     # Pure utility functions (existing)
├── data/                      # Static game data (existing)
└── CleanApp.tsx              # Clean, modular main app component
```

## 🔧 Key Improvements

### 1. Business Logic Extraction

**Before**: Components mixed UI logic with business logic
```tsx
// Old approach - mixed concerns in component
const InfoPanel = () => {
  const attackNode = useGameStore(state => state.attackNode);
  const canAttackNode = useGameStore(state => state.canAttackNode);
  // ... lots of game logic in component
  
  const handleAttack = (nodeId) => {
    if (selectedNode !== null && canAttackNode(nodeId)) {
      attackNode(nodeId);
      // Handle success/error inline
    }
  };
}
```

**After**: Clean separation with custom hooks
```tsx
// New approach - business logic in hooks
const useGameActions = () => {
  const initiateAttack = useCallback((defenderNodeId: number) => {
    // Validation and error handling
    if (selectedNode === null) {
      return { success: false, message: ERROR_MESSAGES.NO_NODE_SELECTED };
    }
    
    if (!canAttackNode(defenderNodeId)) {
      return { success: false, message: ERROR_MESSAGES.INVALID_ATTACK_TARGET };
    }

    attackNode(defenderNodeId);
    return { success: true, message: 'Attack initiated!' };
  }, [selectedNode, canAttackNode, attackNode]);

  return { initiateAttack };
};

// Component focuses only on UI
const NodeInfo = ({ onAttack }) => {
  const { initiateAttack } = useGameActions();
  const { showSuccess, showError } = useGameContext();
  
  const handleAttack = async (nodeId) => {
    const result = initiateAttack(nodeId);
    if (result.success) {
      showSuccess(result.message);
    } else {
      showError(result.message);
    }
  };
};
```

### 2. Enhanced UI Components

**Enhanced Button Component**:
- Multiple variants (primary, secondary, success, danger, warning, ghost)
- Loading states with spinner animation
- Icon support (left/right)
- Full accessibility (min touch targets, ARIA labels)
- Type-safe props interface

```tsx
<Button
  variant="success"
  size="lg"
  loading={isSubmitting}
  leftIcon="⚔️"
  onClick={handleAction}
  disabled={!canPerformAction}
>
  Recruit Commander
</Button>
```

**Toast Notification System**:
- Portal-based rendering for proper z-index
- Auto-dismiss with configurable duration
- Multiple notification types
- Smooth animations
- Context-based global access

### 3. Centralized Configuration

**Constants Management**:
```tsx
export const GAME_CONFIG = {
  INITIAL_GOLD: 500,
  CONQUEST_THRESHOLD: 0.7,
  MAX_COMMANDERS_PER_NODE: 3,
  // ... all game constants centralized
} as const;

export const UI_CONFIG = {
  COLORS: {
    PLAYER: '#22c55e',
    ENEMY: '#ef4444',
    NEUTRAL: '#6b7280',
  },
  MIN_TOUCH_TARGET: 44,
  // ... all UI constants
} as const;
```

### 4. Feature-Specific Components

**Modular Resource Display**:
```tsx
interface ResourceDisplayProps {
  resources: Resources;
  income?: Resources;
  showIncome?: boolean;
  className?: string;
}

export const ResourceDisplay: React.FC<ResourceDisplayProps> = ({
  resources,
  income,
  showIncome = false,
  className = ''
}) => {
  // Pure presentational component with configurable display
};
```

**Self-Contained Game Status**:
```tsx
interface GameStatusProps {
  turn: number;
  phase: Phase;
  onEndTurn: () => void;
  canEndTurn?: boolean;
}

export const GameStatus: React.FC<GameStatusProps> = ({
  turn,
  phase,
  onEndTurn,
  canEndTurn = true
}) => {
  // Handles all game status display logic
  // Visual feedback for different phases
  // Conditional actions based on game state
};
```

### 5. Context-Based Global State

**Game Provider**:
```tsx
export const GameProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const {
    notifications,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    removeNotification,
    clearAllNotifications
  } = useNotifications();

  const contextValue = {
    notifications,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    removeNotification,
    clearAllNotifications
  };

  return (
    <GameContext.Provider value={contextValue}>
      {children}
    </GameContext.Provider>
  );
};
```

## 📊 Benefits Achieved

### 1. **Maintainability**
- **Easy to find code**: Clear directory structure and naming conventions
- **Easy to modify**: Single responsibility principle makes changes isolated
- **Easy to test**: Pure functions and isolated components
- **Clear dependencies**: Explicit imports and type definitions

### 2. **Type Safety**
- **Compile-time error prevention**: Full TypeScript coverage
- **IntelliSense support**: Better developer experience
- **Refactoring confidence**: Type system catches breaking changes
- **Self-documenting code**: Interfaces describe expected data

### 3. **Reusability**
- **Generic UI components**: Button, Toast, Card can be used anywhere
- **Composable business logic**: Custom hooks encapsulate reusable behavior
- **Configurable displays**: Props control component behavior
- **Modular architecture**: Easy to extract components for other projects

### 4. **Performance**
- **Efficient re-renders**: Zustand's selective subscriptions
- **Component isolation**: Changes don't trigger unnecessary updates
- **Optimized callbacks**: useCallback prevents function recreation
- **Minimal prop drilling**: Context and hooks reduce component depth

### 5. **User Experience**
- **Consistent feedback**: Centralized notification system
- **Loading states**: Visual feedback for async operations
- **Error handling**: Graceful error display and recovery
- **Accessibility**: Proper ARIA labels and touch targets

## 🚀 Usage Examples

### Using Enhanced Components

```tsx
import { 
  Button, 
  Toast, 
  ResourceDisplay, 
  GameStatus 
} from '@/components/features';
import { useGameActions, useNotifications } from '@/hooks';

const MyGameComponent = () => {
  const { resources, phase, turn, completeTurn } = useGameActions();
  const { showSuccess } = useNotifications();

  const handleEndTurn = () => {
    const result = completeTurn();
    if (result.success) {
      showSuccess(result.message);
    }
  };

  return (
    <div>
      <ResourceDisplay 
        resources={resources} 
        showIncome={true} 
      />
      
      <GameStatus
        turn={turn}
        phase={phase}
        onEndTurn={handleEndTurn}
      />
      
      <Button
        variant="primary"
        onClick={handleEndTurn}
        disabled={phase !== 'player'}
      >
        End Turn
      </Button>
    </div>
  );
};
```

### Adding New Features

```tsx
// 1. Create a new feature component
const NewFeatureComponent: React.FC<NewFeatureProps> = ({ prop1, prop2 }) => {
  const { gameAction } = useGameActions();
  const { showSuccess } = useGameContext();
  
  const handleAction = () => {
    const result = gameAction();
    if (result.success) {
      showSuccess(result.message);
    }
  };

  return (
    <Card>
      <Button onClick={handleAction}>
        Perform Action
      </Button>
    </Card>
  );
};

// 2. Export from features index
export { NewFeatureComponent } from './NewFeatureComponent';

// 3. Use in any parent component
import { NewFeatureComponent } from '@/components/features';
```

## 🔄 Migration from Old Architecture

### Component Updates

1. **InfoPanel** → **EnhancedInfoPanel**: 
   - Extracted business logic to `useGameActions`
   - Split into modular `NodeInfo` and `CommanderInfo` components
   - Added proper error handling and user feedback

2. **Left/Right Panels** → **Enhanced Versions**:
   - Self-contained with minimal prop dependencies
   - Use feature components for consistent styling
   - Proper loading and disabled states

3. **Button Components** → **EnhancedButton**:
   - Support for variants, sizes, loading states
   - Icon support and accessibility improvements
   - Type-safe props interface

### State Management

1. **Store Actions** → **useGameActions Hook**:
   - Centralized business logic with validation
   - Consistent error handling and user feedback
   - Type-safe return values

2. **Component State** → **Context Providers**:
   - Global notification system
   - Modal state management
   - Reduced prop drilling

## 🎯 Next Steps

### Phase 2 Enhancements (Planned)
- **Error Boundaries**: Graceful error handling and recovery
- **Loading States**: Enhanced loading indicators for async operations
- **Animation System**: Framer Motion integration for smooth transitions
- **Accessibility**: Complete ARIA label coverage and keyboard navigation

### Phase 3 Performance (Planned)
- **React.memo**: Optimize expensive component renders
- **Virtual Scrolling**: Handle large lists of commanders/nodes
- **Code Splitting**: Lazy load feature components
- **Bundle Optimization**: Tree shaking and chunk analysis

## 📝 Developer Guidelines

### Adding New Components

1. **Determine component type**:
   - **UI**: Pure presentational (Button, Card, Input)
   - **Feature**: Business logic + UI (ResourceDisplay, GameStatus)
   - **Layout**: Structural (GameLayout, Panel containers)

2. **Follow naming conventions**:
   - Use descriptive names (`ResourceDisplay` not `Resources`)
   - Prefix enhanced versions (`EnhancedButton`)
   - Group related components (`NodeInfo`, `CommanderInfo`)

3. **Define clear interfaces**:
   - Type all props with TypeScript interfaces
   - Use optional props with sensible defaults
   - Document complex prop behaviors

4. **Implement proper error handling**:
   - Validate inputs and provide feedback
   - Use try-catch for async operations
   - Display user-friendly error messages

### Code Quality Standards

- **TypeScript**: Strict mode enabled, no `any` types
- **ESLint**: Follow established rules and patterns
- **Testing**: Unit tests for hooks and components (planned)
- **Documentation**: JSDoc comments for complex functions
- **Accessibility**: ARIA labels and keyboard navigation

---

This clean architecture implementation transforms Ashes of Aeloria from a functional prototype into a maintainable, scalable, and developer-friendly application that follows modern React best practices and frontend standards.
