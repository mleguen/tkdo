---
stepsCompleted: [1, 2, 3, 4, 5]
inputDocuments:
  - '_bmad-output/analysis/brainstorming-session-2026-01-18.md'
  - 'docs/project-scan-report.json'
  - 'docs/user-guide.md'
  - 'docs/architecture.md'
  - '_bmad-output/project-context.md'
date: 2026-01-18
author: Mael
---

# Product Brief: tkdo

## Executive Summary

**tkdo** is evolving from an occasion-centered gift exchange tool into a **privacy-first, list-centered wishlist platform** that eliminates the coordination chaos of gift-giving across multiple contexts and groups.

Today, families and friend groups struggle with fragmented wishlists scattered across emails, Signal, and WhatsApp—copied, pasted, and impossible to coordinate. Commercial wishlist apps exist but demand trust with sensitive personal information while monetizing user data through advertising.

tkdo offers a different path: an **open-source, community-hosted solution** where each family or friend group runs their own private instance, managed by their "tech person." Users own their data, control exactly who sees what, and never worry about commercial exploitation. The platform becomes the single source of truth for gift ideas, with sophisticated sharing controls that respect the social boundaries between family, friends, colleagues, and in-laws.

**Positioning:** *"Your family's private wishlist. Run by someone you trust."*

---

## Core Vision

### Problem Statement

Gift coordination is broken. People maintain mental lists of things they want, but sharing those lists with the right people—while keeping them hidden from others—requires manual effort across multiple channels. The result:

- **Duplicate gifts** because buyers can't coordinate
- **Copy-paste chaos** across Email, Signal, WhatsApp with no single source of truth
- **Wasted effort** re-entering the same ideas for different occasions (Christmas, birthdays)
- **Self-censorship** because jokes or personal wishes can't be safely shared with all groups

### Problem Impact

Every gift-giving occasion becomes an exercise in logistics rather than generosity. Family members spend time coordinating instead of celebrating. Ideas that would delight recipients never get shared because there's no safe way to share them with some people but not others.

The current tkdo solves the secret santa use case well, but forces users back to fragmented channels for anything beyond that narrow scope.

### Why Existing Solutions Fall Short

| Solution | Limitation |
|----------|-----------|
| **Current tkdo** | Occasion-centric; ideas trapped per-occasion; no sharing with non-participants |
| **Commercial wishlists (Amazon, Giftster)** | Privacy concerns; advertising-driven; sensitive data exploited commercially |
| **Shared documents / group chats** | No coordination features; copy-paste hell; no "purchased" tracking |

None offer: privacy-first architecture + granular group sharing + context-dependent visibility + open source transparency + community-hosted model.

### Proposed Solution

Transform tkdo into a **list-centered platform** where:

1. **The idea list is the persistent asset** — users maintain one list of things they want, not one per occasion
2. **Ideas are rich objects** — title, description, links, availability flag, comment threads
3. **Sharing is granular** — share different portions of your list with different groups, with different permissions
4. **Groups are isolated** — Group A never knows Group B exists; actions don't leak between contexts
5. **Visibility is context-aware** — "purchased" flag visibility adapts to context:
   - *Secret* (owner sees nothing) — secret santa
   - *Anonymous* (owner sees "someone is buying this") — surprise-but-trackable
   - *Transparent* (owner sees "Alice is buying this") — wedding lists
6. **Occasions become optional** — secret santa remains supported, but lists work independently too

### Hosting & Trust Model

**Primary model:** Family/friend instances run by the group's "tech person"
- Deploy once, serve 10-20 users for years
- No central service, no platform dependency
- Trust comes from personal relationship with the instance operator

**Scalable by design:** Same codebase serves:
- Family instances (10-20 users)
- Community instances (50-200 users)
- Cooperative instances (larger scale, shared governance)

**End-user experience:** Invisible infrastructure
- Receive invite link → sign up → start using
- "Who hosts this?" is never a question for 95% of users

**Instance privacy:** No public directory, no federation, no discovery
- Each instance is private, invite-only by design
- You join *your family's* tkdo, not "a platform"

### Key Differentiators

| Differentiator | Why It Matters |
|----------------|----------------|
| **Privacy-first, open source** | Users trust tkdo with sensitive wishes; no commercial exploitation |
| **Community-hosted, not SaaS** | No central service to depend on or distrust; your data on your instance |
| **Single source of truth** | End copy-paste chaos; one list, many views |
| **Granular group isolation** | Share jokes with friends without in-laws seeing them |
| **Context-dependent visibility** | Same platform works for secret santa AND wedding lists |
| **No commercial agenda** | User-added links are helpful; platform never pushes ads or tracks |
| **Accessible self-hosting** | One-click deploy options; clear cost expectations; boring upgrades |

### Maintainer Philosophy

The tkdo maintainer (Mael) maintains **code**, not **infrastructure**:
- Open source development and releases
- Clear documentation for deployment
- Boring upgrades: semantic versioning, automated migrations, `git pull && ./upgrade`
- Community owns the hosting story

---

## Target Users

### Primary Users

#### Marie-Claire, 67 — The List Owner Who "Isn't Technical"

**Context:** Retired grandmother. Uses WhatsApp to share photos with family. Can barely attach a file to an email. Has never installed an app herself.

**Current pain:**
- Her daughter used to text her asking "What do you want for your birthday?" — now she has to remember and repeat the same list to three different family branches
- She once got two identical scarves because her sons didn't coordinate

**How she uses tkdo:**
- Received an invite link from her nephew Julien
- Clicked it, created an account (email + password — Google login feels "unsafe" to her)
- Now adds ideas whenever she thinks of them: "The blue teapot from the market on Rue Mouffetard"
- Doesn't understand "groups" — just knows her list is visible to family

**Success moment:** "I didn't have to repeat myself this year. And no duplicate gifts!"

---

#### Julien, 34 — The Instance Operator / Family Hero

**Context:** Software developer. The family member everyone calls when the printer doesn't work. Volunteered to "fix Christmas" after last year's duplicate gift disaster.

**Current pain:**
- Spends December fielding WhatsApp messages: "What does Grandma want?" / "Did anyone already buy the teapot?"
- Tried shared Google Docs — nobody updated them
- Considered commercial wishlist apps but doesn't trust them with family data

**How he uses tkdo:**
- Deployed an instance on Railway in 20 minutes
- Invited extended family (parents, siblings, aunts, cousins) — about 15 people
- Set himself as admin, added his sister as co-admin
- Created two groups: "Côté Papa" and "Côté Maman" so the families don't see each other's gift coordination

**Success moment:** "Zero coordination messages this December. The app just works."

---

#### Sophie, 29 — The Cross-Group Power User

**Context:** Marketing manager. Married with in-laws. Has a tight friend group that does a yearly secret santa.

**Current pain:**
- Maintains three mental wishlists: one for her parents, one for in-laws (different vibe), one for friends
- Her in-laws once saw a joke gift idea meant for friends. Awkward.

**How she uses tkdo:**
- One account, three groups: "Ma famille", "Belle-famille", "Les copains"
- Shares different ideas with each group — some overlap, some exclusive
- Marks visibility carefully: the lingerie is friends-only; the KitchenAid is family-only (in-laws already bought one)

**Success moment:** "I can finally be myself with each group without worrying about leaks."

---

### Secondary Users

#### Thomas, 42 — The Gift Giver (Non-Participant)

**Context:** Sophie's uncle. Lives abroad. Won't attend Christmas but still sends gifts.

**Current pain:**
- Gets a forwarded WhatsApp screenshot of "Sophie's list" — already outdated
- No way to mark what he's giving; risks duplicates

**How he uses tkdo:**
- Julien adds him to the instance with limited permissions (can view lists, can mark "I'm giving this", cannot add ideas)
- Sees real-time list, marks the teapot as "being given"
- No secret santa participation — just a gift giver

**Success moment:** "Finally I know what's already taken before I buy."

---

#### Read-Only Guest (Potential Future)

**Context:** A distant relative or acquaintance who wants to give a gift but doesn't want to create an account.

**How it might work:**
- Receives a time-limited, authenticated link (not public)
- Can view a specific list, mark items as "giving"
- No account required, but link expires

**Security consideration:** Must not be truly public. Link = authentication token with expiration.

---

### User Journey

| Stage | Marie-Claire (End User) | Julien (Instance Operator) |
|-------|------------------------|---------------------------|
| **Discovery** | Nephew sends WhatsApp: "Click this link for family gifts" | Finds tkdo on GitHub, sees "one-click deploy" |
| **Onboarding** | Click link → signup → done | Deploy → invite family → create groups |
| **Core usage** | Add ideas, browse others' lists, get notified | Occasional admin tasks, mostly a regular user |
| **Success moment** | No duplicates, no repeating herself | Zero coordination messages |
| **Long-term** | Uses it every year, adds ideas throughout the year | Upgrades once a year, "it just works" |

---

## Success Metrics

### Core Success Criteria

**The rewrite is successful when:**

1. **Family-in-law adopts tkdo** — They abandon the email/WhatsApp coordination nightmare and actually use the app
2. **Secret santa consideration** — Family-in-law finds enough value in lists that they consider using the secret santa feature too

---

### User Success Signals

| Signal | How You'll Know |
|--------|-----------------|
| **Adoption** | Family-in-law members create accounts and add ideas |
| **Engagement** | Ideas get marked as "being given" — coordination is happening |
| **Expansion** | They ask about secret santa: "Can we use this for Christmas too?" |
| **Retention** | They come back next year without prompting |

---

### Project Health Signals

| Signal | How You'll Know |
|--------|-----------------|
| **Maintainable** | You're not dreading December because of support requests |
| **Upgradeable** | You can ship improvements without breaking family instances |
| **Documentable** | Someone other than you could deploy an instance from docs alone |

---

### Quality Guardrails

| Guardrail | Failure Mode to Avoid |
|-----------|----------------------|
| **Zero group leaks** | Family-in-law never sees something meant for friends |
| **Reliable during peak** | No outages during November-December gift season |
| **Data integrity** | No lost ideas, no corrupted lists |

---

### Explicit Non-Metrics

Things we deliberately don't measure:

- **User count** — Not relevant; quality over quantity
- **Growth rate** — Not a growth product
- **Revenue** — Passion project, no commercial goals
- **Analytics** — Privacy-first means no tracking

---

## MVP Scope

### Core Features (Must Have)

| Feature | Why Essential |
|---------|---------------|
| **List-centered model** | The core pivot — users own one persistent list |
| **Rich ideas** (title, description, link) | Meaningful gift descriptions |
| **Comment threads on ideas** | Coordination between gift givers |
| **"Being given" flag** (anonymous mode) | Prevent duplicates — the core problem |
| **Groups with isolation** | Share with family ≠ share with in-laws |
| **Per-idea visibility** | Share specific ideas with specific groups (Sophie's use case) |
| **Invite flow** | Marie-Claire needs to join easily |
| **Email notifications** | Existing feature, must keep working |

### Sharing Model (Refined)

- User has **one list** of ideas
- Each idea has **visibility**: which groups can see it
- **Default**: new idea visible to all user's groups (easy path)
- User can **restrict** any idea to specific groups
- **Within a group**: everyone sees the same ideas, same permissions
- Visibility is **per-group**, not per-individual

### Out of Scope (Deferred to v2+)

| Feature | Why Defer |
|---------|-----------|
| **One-click deploy** | Mael deploys manually for now |
| **OAuth (Google login)** | Email/password works |
| **Signal/WhatsApp notifications** | Email works for now |
| **Visibility modes (secret/transparent)** | Start with anonymous only |
| **Occasions / Secret Santa enhancements** | Keep existing, don't enhance yet |
| **Guest/read-only access** | Full accounts first |
| **Granular permissions per group** | Start simple: view + mark + comment for all |
| **Per-individual visibility** | Groups only, no per-person exceptions |

### MVP Success Gate

**MVP is validated when:**
- Family-in-law creates accounts and adds ideas
- Comments are used to coordinate ("I'll get the blue one")
- Items get marked as "being given"
- Sophie comfortably shares different ideas with different groups
- No one asks "but what does X want?" via WhatsApp

### Critical Test Cases (Group Isolation)

These must pass before launch:

1. Sophie creates idea visible to Group A only → Group B member cannot see it
2. Group B member views Sophie's list → restricted idea NOT visible
3. Sophie adds Group B to idea visibility → now visible to Group B
4. Sophie removes Group A from idea → no longer visible to Group A

### Future Vision

| Phase | Features |
|-------|----------|
| **v2: Broader adoption** | One-click deploy, OAuth, improved permissions |
| **v2: Richer notifications** | Signal/WhatsApp channels |
| **v2: Visibility modes** | Secret (secret santa), Transparent (wedding lists) |
| **v3: Advanced** | Guest access, per-individual visibility, API, PWA |

---

