/**
 * k6 Performance Baseline Capture Script
 *
 * Captures response time baselines for key API operations.
 * Run against Docker dev environment before v2 code changes.
 *
 * Usage:
 *   ./k6 run perf/baseline.js
 *   ./k6 run -e ITERATIONS=10 perf/baseline.js  # fewer iterations
 *
 * Prerequisites:
 *   - Docker dev environment running (docker compose up -d front)
 *   - Fixtures loaded with perf data (./console fixtures --perf)
 *
 * Data Conditions (met by PerfFixture):
 *   - Occasion "Perf Test" with 10+ participants
 *   - User "bob" with 20+ ideas
 */

import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Trend, Counter } from 'k6/metrics';

// Custom metrics for each scenario
const loginDuration = new Trend('login_duration');
const viewOccasionDuration = new Trend('view_occasion_duration');
const listIdeasDuration = new Trend('list_ideas_duration');
const addIdeaDuration = new Trend('add_idea_duration');
const editIdeaDuration = new Trend('edit_idea_duration');
const deleteIdeaDuration = new Trend('delete_idea_duration');
const adminListUsersDuration = new Trend('admin_list_users_duration');
const adminListOccasionsDuration = new Trend('admin_list_occasions_duration');
const errors = new Counter('errors');

// Configuration
// Default to Docker internal network (k6 runs in container alongside other services)
// The front container proxies /api/* to the slim-web container
const BASE_URL = __ENV.BASE_URL || 'http://front/api';
const ITERATIONS = __ENV.ITERATIONS ? parseInt(__ENV.ITERATIONS) : 100;

// Test credentials (from fixtures)
const ADMIN_USER = { identifiant: 'alice', mdp: 'mdpalice' };
const REGULAR_USER = { identifiant: 'bob', mdp: 'mdpbob' };

// Minimum test data requirements for valid baseline
const MIN_PARTICIPANTS = 10;
const MIN_IDEAS = 20;

export const options = {
  scenarios: {
    baseline_capture: {
      executor: 'per-vu-iterations',
      vus: 1,
      iterations: ITERATIONS,
      maxDuration: '30m',
    },
  },
  // Include p99 in summary statistics (k6 doesn't calculate by default)
  summaryTrendStats: ['avg', 'min', 'med', 'max', 'p(90)', 'p(95)', 'p(99)'],
  thresholds: {
    // Document thresholds - these won't fail the test, just record
    login_duration: ['p(95)<2000', 'p(99)<3000'],
    view_occasion_duration: ['p(95)<2000', 'p(99)<3000'],
    list_ideas_duration: ['p(95)<2000', 'p(99)<3000'],
    add_idea_duration: ['p(95)<2000', 'p(99)<3000'],
    edit_idea_duration: ['p(95)<2000', 'p(99)<3000'],
    delete_idea_duration: ['p(95)<2000', 'p(99)<3000'],
    admin_list_users_duration: ['p(95)<2000', 'p(99)<3000'],
    admin_list_occasions_duration: ['p(95)<2000', 'p(99)<3000'],
  },
};

/**
 * Setup function - validates test data conditions once before running tests.
 * Warns if fixtures don't meet baseline requirements (run ./console fixtures --perf).
 */
export function setup() {
  console.log('Validating test data conditions...');

  // Login as admin to check data
  const adminRes = http.post(
    `${BASE_URL}/connexion`,
    `identifiant=${ADMIN_USER.identifiant}&mdp=${ADMIN_USER.mdp}`,
    { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }
  );

  if (adminRes.status !== 200) {
    console.error(`Setup failed: Cannot login as admin - ${adminRes.status}`);
    return { valid: false };
  }

  const adminData = JSON.parse(adminRes.body);
  const headers = {
    headers: {
      Authorization: `Bearer ${adminData.token}`,
      'Content-Type': 'application/json',
    },
  };

  // Check occasions for participant count
  const occasionsRes = http.get(`${BASE_URL}/occasion`, headers);
  if (occasionsRes.status === 200) {
    const occasions = JSON.parse(occasionsRes.body);
    const maxParticipants = Math.max(...occasions.map((o) => o.participants?.length || 0), 0);
    if (maxParticipants < MIN_PARTICIPANTS) {
      console.warn(
        `⚠️  DATA CONDITION NOT MET: No occasion has ${MIN_PARTICIPANTS}+ participants (max found: ${maxParticipants}). ` +
          `Run './console fixtures --perf' to create proper test data.`
      );
    } else {
      console.log(`✓ Occasion with ${maxParticipants} participants found (requirement: ${MIN_PARTICIPANTS}+)`);
    }
  }

  // Login as bob (the test user) to check their ideas
  const bobRes = http.post(
    `${BASE_URL}/connexion`,
    `identifiant=${REGULAR_USER.identifiant}&mdp=${REGULAR_USER.mdp}`,
    { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }
  );

  if (bobRes.status === 200) {
    const bobData = JSON.parse(bobRes.body);
    const bobHeaders = {
      headers: {
        Authorization: `Bearer ${bobData.token}`,
        'Content-Type': 'application/json',
      },
    };

    // Check bob's ideas count
    const ideasRes = http.get(`${BASE_URL}/idee`, bobHeaders);
    if (ideasRes.status === 200) {
      const ideas = JSON.parse(ideasRes.body);
      if (ideas.length < MIN_IDEAS) {
        console.warn(
          `⚠️  DATA CONDITION NOT MET: User 'bob' has only ${ideas.length} ideas (requirement: ${MIN_IDEAS}+). ` +
            `Run './console fixtures --perf' to create proper test data.`
        );
      } else {
        console.log(`✓ User 'bob' has ${ideas.length} ideas (requirement: ${MIN_IDEAS}+)`);
      }
    }
  }

  console.log('Setup complete. Starting baseline capture...\n');
  return { valid: true };
}

/**
 * Login and get JWT token
 */
function login(credentials) {
  const res = http.post(
    `${BASE_URL}/connexion`,
    `identifiant=${credentials.identifiant}&mdp=${credentials.mdp}`,
    {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    }
  );

  if (res.status !== 200) {
    errors.add(1);
    console.error(`Login failed for ${credentials.identifiant}: ${res.status} - ${res.body}`);
    return null;
  }

  const body = JSON.parse(res.body);
  return { token: body.token, user: body.utilisateur };
}

/**
 * Get auth headers with token
 */
function authHeaders(token) {
  return {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  };
}

/**
 * Get form-encoded auth headers with token
 */
function authHeadersForm(token) {
  return {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/x-www-form-urlencoded',
    },
  };
}

export default function () {
  let adminAuth, userAuth;
  let createdIdeaId;
  let occasionId;

  // ========================================
  // Login Flow
  // ========================================
  group('Login Flow', function () {
    // Admin login
    const adminLoginStart = Date.now();
    adminAuth = login(ADMIN_USER);
    loginDuration.add(Date.now() - adminLoginStart);

    check(adminAuth, {
      'admin login successful': (a) => a !== null && a.token !== null,
    });

    // Regular user login
    const userLoginStart = Date.now();
    userAuth = login(REGULAR_USER);
    loginDuration.add(Date.now() - userLoginStart);

    check(userAuth, {
      'user login successful': (a) => a !== null && a.token !== null,
    });
  });

  if (!adminAuth || !userAuth) {
    console.error('Login failed, skipping remaining tests');
    return;
  }

  // ========================================
  // Admin: List Occasions (get occasion ID for later tests)
  // ========================================
  group('Admin List Occasions', function () {
    const start = Date.now();
    const res = http.get(`${BASE_URL}/occasion`, authHeaders(adminAuth.token));
    adminListOccasionsDuration.add(Date.now() - start);

    const success = check(res, {
      'admin list occasions status 200': (r) => r.status === 200,
    });

    if (success && res.status === 200) {
      const occasions = JSON.parse(res.body);
      // Use first occasion (perf fixtures ensure adequate test data)
      if (occasions.length > 0) {
        occasionId = occasions[0].id;
      }
    }
  });

  // ========================================
  // View Occasion with Participants
  // ========================================
  group('View Occasion', function () {
    if (occasionId) {
      const start = Date.now();
      const res = http.get(`${BASE_URL}/occasion/${occasionId}`, authHeaders(userAuth.token));
      viewOccasionDuration.add(Date.now() - start);

      check(res, {
        'view occasion status 200': (r) => r.status === 200,
      });
    } else {
      console.warn('No occasion found to view');
    }
  });

  // ========================================
  // List Ideas (--perf fixtures ensure bob has 20+ ideas)
  // ========================================
  group('List Ideas', function () {
    const start = Date.now();
    const res = http.get(`${BASE_URL}/idee`, authHeaders(userAuth.token));
    listIdeasDuration.add(Date.now() - start);

    check(res, {
      'list ideas status 200': (r) => r.status === 200,
    });
  });

  // ========================================
  // Add Idea (POST /api/idee)
  // ========================================
  group('Add Idea', function () {
    if (occasionId && userAuth.user) {
      // Use form-encoded data as the API expects
      // idUtilisateur = recipient, idAuteur = creator (same user for self-ideas)
      const ideaData = `idUtilisateur=${userAuth.user.id}&idAuteur=${userAuth.user.id}&idOccasion=${occasionId}&description=Baseline+test+idea+${Date.now()}`;

      const start = Date.now();
      const res = http.post(`${BASE_URL}/idee`, ideaData, authHeadersForm(userAuth.token));
      addIdeaDuration.add(Date.now() - start);

      const success = check(res, {
        'add idea status 200': (r) => r.status === 200,
      });

      if (success && res.status === 200) {
        const idea = JSON.parse(res.body);
        createdIdeaId = idea.id;
      } else {
        console.warn(`Add idea failed: ${res.status} - ${res.body}`);
      }
    } else {
      console.warn('No occasion or user found for add idea test');
    }
  });

  // ========================================
  // Edit Idea - POST to /idee with id field for updates
  // ========================================
  group('Edit Idea', function () {
    if (createdIdeaId && occasionId && userAuth.user) {
      // POST with id field to update existing idea
      const updateData = `id=${createdIdeaId}&idUtilisateur=${userAuth.user.id}&idAuteur=${userAuth.user.id}&idOccasion=${occasionId}&description=Updated+baseline+test+idea+${Date.now()}`;

      const start = Date.now();
      const res = http.post(`${BASE_URL}/idee`, updateData, authHeadersForm(userAuth.token));
      editIdeaDuration.add(Date.now() - start);

      check(res, {
        'edit idea status 200': (r) => r.status === 200,
      });
    }
  });

  // ========================================
  // Delete Idea (POST /api/idee/{id}/suppression)
  // ========================================
  group('Delete Idea', function () {
    if (createdIdeaId) {
      const start = Date.now();
      const res = http.post(`${BASE_URL}/idee/${createdIdeaId}/suppression`, '', authHeadersForm(userAuth.token));
      deleteIdeaDuration.add(Date.now() - start);

      check(res, {
        'delete idea status 200': (r) => r.status === 200,
      });
    }
  });

  // ========================================
  // Admin: List Users
  // ========================================
  group('Admin List Users', function () {
    const start = Date.now();
    const res = http.get(`${BASE_URL}/utilisateur`, authHeaders(adminAuth.token));
    adminListUsersDuration.add(Date.now() - start);

    check(res, {
      'admin list users status 200': (r) => r.status === 200,
    });
  });

  // Small delay between iterations
  sleep(0.1);
}

/**
 * Generate summary for baseline.json
 */
export function handleSummary(data) {
  const scenarios = {};

  // Extract metrics for each scenario
  const metricMappings = {
    login: 'login_duration',
    view_occasion: 'view_occasion_duration',
    list_ideas: 'list_ideas_duration',
    add_idea: 'add_idea_duration',
    edit_idea: 'edit_idea_duration',
    delete_idea: 'delete_idea_duration',
    admin_list_users: 'admin_list_users_duration',
    admin_list_occasions: 'admin_list_occasions_duration',
  };

  for (const [name, metricName] of Object.entries(metricMappings)) {
    const metric = data.metrics[metricName];
    if (metric && metric.values) {
      scenarios[name] = {
        avg_ms: Math.round(metric.values.avg || 0),
        p95_ms: Math.round(metric.values['p(95)'] || 0),
        p99_ms: Math.round(metric.values['p(99)'] || 0),
        min_ms: Math.round(metric.values.min || 0),
        max_ms: Math.round(metric.values.max || 0),
      };
    }
  }

  const baseline = {
    captured: new Date().toISOString().split('T')[0],
    environment: {
      type: 'docker-dev',
      base_url: BASE_URL,
      iterations_per_scenario: ITERATIONS,
    },
    scenarios: scenarios,
    summary: {
      total_requests: data.metrics.http_reqs ? data.metrics.http_reqs.values.count : 0,
      total_errors: data.metrics.errors ? data.metrics.errors.values.count : 0,
      duration_seconds: data.state ? Math.round(data.state.testRunDurationMs / 1000) : 0,
    },
  };

  return {
    'docs/performance-baseline.json': JSON.stringify(baseline, null, 2),
    stdout: textSummary(data, { indent: '  ', enableColors: true }),
  };
}

/**
 * Simple text summary
 */
function textSummary(data, options) {
  let output = '\n=== Performance Baseline Summary ===\n\n';

  const scenarios = [
    ['Login', 'login_duration'],
    ['View Occasion', 'view_occasion_duration'],
    ['List Ideas', 'list_ideas_duration'],
    ['Add Idea', 'add_idea_duration'],
    ['Edit Idea', 'edit_idea_duration'],
    ['Delete Idea', 'delete_idea_duration'],
    ['Admin List Users', 'admin_list_users_duration'],
    ['Admin List Occasions', 'admin_list_occasions_duration'],
  ];

  for (const [name, metricName] of scenarios) {
    const metric = data.metrics[metricName];
    if (metric && metric.values) {
      output += `${name}:\n`;
      output += `  avg: ${Math.round(metric.values.avg)}ms\n`;
      output += `  p95: ${Math.round(metric.values['p(95)'])}ms\n`;
      output += `  p99: ${Math.round(metric.values['p(99)'])}ms\n\n`;
    }
  }

  output += `Total requests: ${data.metrics.http_reqs ? data.metrics.http_reqs.values.count : 0}\n`;
  output += `Errors: ${data.metrics.errors ? data.metrics.errors.values.count : 0}\n`;

  return output;
}
