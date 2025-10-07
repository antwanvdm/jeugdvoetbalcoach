# LineupGeneratorService Documentation

## Overview

The `LineupGeneratorService` is responsible for automatically generating balanced football lineups for 4-quarter matches. It considers multiple factors including player rotation, weight distribution, position preferences, and keeper history.

## Key Features

### 🥅 **Intelligent Keeper Selection**

-   Prioritizes players who **didn't keep goal in the previous match**
-   Considers **historical keeper appearances** (least experienced first)
-   Applies **weight balance** to avoid clustering similar physical levels
-   Ensures **4 different keepers** across the 4 quarters

### ⚽ **Smart Player Rotation**

-   **Non-keepers**: bench exactly 2 quarters (alternating Q1+Q3 vs Q2+Q4)
-   **Keepers**: bench exactly 1 quarter (not their keeper quarter)
-   **Weight-based sorting** for better physical distribution

### 🏃‍♂️ **Formation & Position Management**

-   **Fixed formation**: 1 Keeper, 2 Defenders, 1 Midfielder, 2 Attackers
-   **Position preference**: Players get their favorite positions when possible
-   **Fallback system**: Assigns suitable alternatives when needed

### ⚖️ **Weight Balance System**

-   **Penalty system**: Heavy penalties for >2 players with same weight
-   **Distribution optimization**: Aims for diverse physical levels per quarter
-   **Multi-level sorting**: Considers balance, individual weight, and name consistency

## Architecture

### 🏗️ **Clean OOP Design**

-   **Immutable operations**: No "by reference" parameters
-   **Single responsibility**: Each method has one clear purpose
-   **Proper class separation**: Support classes in dedicated namespace
-   **Testable**: All logic separated into focused, testable methods

### 📁 **File Structure**

```
app/Services/
├── LineupGeneratorService.php           # Main service class
└── LineupGenerator/
    ├── QuarterAssignmentData.php        # Data transfer object
    └── AssignmentResult.php             # Result wrapper class
```

### 🎯 **Class Responsibilities**

**`LineupGeneratorService`** - Main orchestrator

-   Coordinates the entire lineup generation process
-   Contains business logic and algorithms
-   Manages player data and statistics

**`QuarterAssignmentData`** - State holder

-   Immutable data container for quarter assignments
-   Tracks selected players and their assignments
-   Provides safe state management

**`AssignmentResult`** - Result wrapper

-   Encapsulates success/failure with data
-   Enables clean functional-style operations
-   Prevents side effects

### 📊 **Data Flow**

```
generateLineup()
├── loadPlayersData()
├── selectKeepers()
├── createBenchPlan()
└── assignPlayersToQuarters()
    └── (for each quarter)
        ├── withBenchedPlayers()
        ├── withAssignedKeeper()
        ├── withAssignedOutfieldPlayers()
        └── withAssignedRemainingPlayers()
```

## Usage

```php
// In your controller
public function store(Request $request, LineupGeneratorService $lineupGenerator): RedirectResponse
{
    $match = FootballMatch::create($validated);
    $lineupGenerator->generateLineup($match);
    return redirect()->route('football-matches.show', $match);
}
```

## Constants

-   `KEEPER_POSITION_ID = 1`
-   `DEFENDER_POSITION_ID = 2`
-   `MIDFIELDER_POSITION_ID = 3`
-   `ATTACKER_POSITION_ID = 4`
-   `FORMATION_NEEDS = ['defender' => 2, 'midfielder' => 1, 'attacker' => 2]`

## Methods Overview

### Core Methods

-   `generateLineup(FootballMatch $match)` - Main entry point
-   `loadPlayersData()` - Loads players and statistics
-   `selectKeepers()` - Intelligent keeper selection
-   `createBenchPlan(Collection $keepers)` - Creates rotation plan

### Assignment Methods

-   `buildQuarterAssignments(...)` - Builds assignments per quarter
-   `withBenchedPlayers(...)` - Adds bench assignments
-   `withAssignedKeeper(...)` - Assigns quarter keeper
-   `withAssignedOutfieldPlayers(...)` - Assigns field players
-   `tryAssignPlayerForRole(...)` - Smart role-based assignment

### Helper Methods

-   `calculateWeightBalance(array $playerIds)` - Weight distribution scoring
-   `getPlayerFavoriteRole(?int $positionId)` - Role mapping
-   `determinePositionId(Player $player, string $role)` - Position assignment
-   `getLastMatchKeepers()` - Previous match keeper lookup

## Benefits

✅ **Maintainable**: Clean separation of concerns
✅ **Testable**: Each method can be unit tested
✅ **Extensible**: Easy to add new features
✅ **Reliable**: Immutable operations prevent side effects
✅ **Smart**: Considers multiple optimization factors
✅ **Fair**: Ensures balanced rotation and opportunities

## Improvements Made

### Before (Controller Chaos)

-   300+ lines of inline code
-   Nested closures and complex logic
-   "By reference" parameters everywhere
-   Hard to test and maintain
-   Mixed responsibilities

### After (Clean Service)

-   Focused, single-purpose methods
-   Immutable data operations
-   Clear data flow and state management
-   Comprehensive documentation
-   Easy to test and extend
