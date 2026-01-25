# Performance Testing

This directory contains k6 performance test scripts for capturing baselines and regression testing.

## Prerequisites

k6 runs via Docker using the `./k6` wrapper script. No host installation required.

## Running Baseline Capture

### 1. Ensure Docker dev environment is running

```bash
docker compose up -d front
```

### 2. Load fixtures (if not already loaded)

```bash
./console fixtures
```

### 3. Run baseline capture

```bash
./k6 run perf/baseline.js
```

This will:
- Run 100 iterations of each scenario
- Output results to console
- Save baseline to `docs/performance-baseline.json`

### Custom options

```bash
# Fewer iterations (faster, less accurate)
./k6 run -e ITERATIONS=10 perf/baseline.js

# Different base URL (e.g., if running k6 on host)
./k6 run -e BASE_URL=http://localhost:8080/api perf/baseline.js

# Save raw results to JSON
./k6 run --out json=perf/results.json perf/baseline.js
```

## Test Scenarios

| Scenario | Endpoint | Description |
|----------|----------|-------------|
| Login | POST /api/connexion | User authentication |
| View Occasion | GET /api/occasion/{id} | Occasion with participants |
| List Ideas | GET /api/utilisateur/{id}/idees | User's idea list |
| Add Idea | POST /api/idee | Create new idea |
| Edit Idea | PUT /api/idee/{id} | Update idea |
| Delete Idea | DELETE /api/idee/{id} | Remove idea |
| Admin List Users | GET /api/utilisateurs | Admin user listing |
| Admin List Occasions | GET /api/occasions | Admin occasion listing |

## Output

Results are saved to `docs/performance-baseline.json`:

```json
{
  "captured": "2026-01-25",
  "environment": {
    "type": "docker-dev",
    "base_url": "http://localhost:8080/api",
    "iterations_per_scenario": 100
  },
  "scenarios": {
    "login": {
      "iterations": 200,
      "avg_ms": 45,
      "p95_ms": 78,
      "p99_ms": 120
    },
    ...
  }
}
```

## Usage in CI/Regression Testing

For Story 9.8 (post-migration performance regression testing), use this same script and compare results against the baseline:

```bash
# Run regression test
./k6 run perf/baseline.js

# Compare results manually or use k6 cloud for historical comparison
```

Thresholds are set to document (not fail) so baselines can be captured regardless of current performance.
