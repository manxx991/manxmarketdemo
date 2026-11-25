---
name: Neutral Operational System
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#45464d'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#76777d'
  outline-variant: '#c6c6cd'
  surface-tint: '#565e74'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#131b2e'
  on-primary-container: '#7c839b'
  inverse-primary: '#bec6e0'
  secondary: '#0051d5'
  on-secondary: '#ffffff'
  secondary-container: '#316bf3'
  on-secondary-container: '#fefcff'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#00201d'
  on-tertiary-container: '#0c9488'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2fd'
  primary-fixed-dim: '#bec6e0'
  on-primary-fixed: '#131b2e'
  on-primary-fixed-variant: '#3f465c'
  secondary-fixed: '#dbe1ff'
  secondary-fixed-dim: '#b4c5ff'
  on-secondary-fixed: '#00174b'
  on-secondary-fixed-variant: '#003ea8'
  tertiary-fixed: '#89f5e7'
  tertiary-fixed-dim: '#6bd8cb'
  on-tertiary-fixed: '#00201d'
  on-tertiary-fixed-variant: '#005049'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: 0em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: 0em
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: 0em
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
    letterSpacing: 0em
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
    letterSpacing: 0em
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
    letterSpacing: 0.02em
  data-tabular:
    fontFamily: JetBrains Mono
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
    letterSpacing: -0.01em
  data-tabular-lg:
    fontFamily: JetBrains Mono
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 26px
    letterSpacing: -0.02em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-dense: 0.5rem
  margin: 1.5rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 0.75rem
  space-lg: 1rem
  space-xl: 1.5rem
---

## Brand & Style

This design system is engineered for heavy transactional efficiency, high data density, and prolonged daily usage in enterprise wholesale and point-of-sale operations. The operational rhythm requires complete visual stability: zero novelty distractions, instantaneous component recognition, and strict cognitive clarity.

The visual style blends **Corporate / Modern** operational rigor with **Minimalist** discipline. It relies on crisp grid lines, clear visual hierarchy, disciplined white space, and purposeful semantic accents. The interface acts as an invisible, reliable tool that maximizes speed, minimizes operator error, and ensures effortless scanning across inventory tables, checkout registers, wholesale order batches, and authentication screens.

## Colors

The palette is anchored by a high-contrast slate neutral system designed to resist eye fatigue during 8- to 12-hour shifts. 

- **Primary (`#0F172A`)**: Slate 900. Serves as the primary operational ink for high-priority actions, system headers, active tabs, and dominant structural elements.
- **Secondary (`#2563EB`)**: Cobalt Blue. Reserved for functional interactive targets, inline links, active focus rings, and primary action affordances within checkout and order pipelines.
- **Tertiary (`#0D9488`)**: Deep Teal. Applied to positive operational states, successful balance reconciliation, completed inventory receipts, and paid transactional indicators.
- **Neutral (`#64748B`)**: Slate 500. Drives secondary typography, muted structural borders, table division lines, and inactive component treatments.

### Functional Palette
- **Canvas Base**: `#F8FAFC` (Slate 50)
- **Surface Elevation**: `#FFFFFF`
- **Border / Hairlines**: `#E2E8F0` (Slate 200)
- **Border Strong**: `#CBD5E1` (Slate 300)
- **Error / Critical Alert**: `#DC2626` (Red 600)
- **Warning / Pending**: `#D97706` (Amber 600)
- **Success / Settled**: `#16A34A` (Green 600)

## Typography

Typography is calibrated for instantaneous scannability across dense tabular rows, barcode outputs, inventory manifests, and point-of-sale totals.

- **Primary Interface Font**: `Inter` handles all structural levels, from master system titles to dense field labels. Use font weights `400` (body copy/descriptions), `500` (labels/column titles), and `600` (structural headers, totals, and primary CTAs).
- **Tabular & Monospace Font**: `JetBrains Mono` is enforced for SKUs, serial identifiers, monetary units, currency balances, barcode labels, and POS calculation feeds. It guarantees column alignment without jitter when values change dynamically.
- **Micro-Copy Rules**: Form labels use `label-md` uppercase styling with slight letter spacing (`0.02em`) when positioned directly above high-density data fields.

## Layout & Spacing

The layout employs a strict **fluid operational grid** with dense spacing defaults optimized for split-screen POS cash registers, full-screen inventory tables, and consolidated login panes.

### Breakpoints & Canvas Model
- **Desktop (>= 1280px)**: 12-column grid, fluid width with an optional 1600px max container for administrative analytics. Gutter: `1rem` (standard) or `0.5rem` (`gutter-dense` for live registers and inventory matrices). Margin: `1.5rem`.
- **Tablet / POS Terminal (768px - 1279px)**: 8-column layout. Split layout prioritizes the shopping cart/checkout sheet (fixed 380px or 40% width) and product catalog (fluid 60% width).
- **Mobile (< 768px)**: 4-column layout. Gutter: `0.75rem`, Margin: `1rem`. Collapses auxiliary sidebars into off-canvas drawers; checkout summaries pin to the bottom viewport.

### Density Tiers
- **Standard Admin**: Spacing scale uses `space-md` (`0.75rem`) for row spacing and `space-lg` (`1rem`) for container padding.
- **Dense Transactional (POS/Grosir Grid)**: Row spacing shrinks to `space-sm` (`0.5rem`) with vertical cell paddings constrained to `0.375rem` (`6px`) to ensure at least 15 visible line items without scrolling.

## Elevation & Depth

To avoid visual pollution and conserve rendering speed on lower-spec POS hardware, depth is communicated primarily through **low-contrast outlines** and **tonal surface separation**, not blurred shadows.

- **Level 0 (Canvas Base)**: `#F8FAFC`. Background for entire application window.
- **Level 1 (Surface Cards & Grids)**: `#FFFFFF` encased in a crisp 1px solid border of `#E2E8F0`. Shadow: `none`.
- **Level 2 (Dropdowns, Popovers & Context Menus)**: `#FFFFFF` encased in a 1px border of `#CBD5E1` with an ambient hard-edge shadow: `0px 4px 6px -1px rgba(15, 23, 42, 0.08), 0px 2px 4px -2px rgba(15, 23, 42, 0.04)`.
- **Level 3 (Modal Sheets & Transaction Confirmation Overlays)**: `#FFFFFF` resting against a backdrop scrim of `#0F172A` at `40% opacity`. Shadow: `0px 10px 15px -3px rgba(15, 23, 42, 0.12), 0px 4px 6px -4px rgba(15, 23, 42, 0.06)`.
- **Dividers**: All interior divisions (table row separators, panel splits, card headers) use a 1px hairline rule of `#F1F5F9`.

## Shapes

The interface embraces a **Soft (Level 1)** geometric standard. Crisp corners with minute rounding preserve pixel-level alignment, maximizing readable real estate on POS monitors and data screens.

- **Micro Controls (Inputs, Badges, Buttons, Selects)**: `0.25rem` (`4px`) border-radius.
- **Structural Containers (Cards, Tables, Slide-out Drawers)**: `0.375rem` (`6px`) border-radius.
- **Modals & Dialogs**: `0.5rem` (`8px`) border-radius.
- **Pill Badges (Specific Status Chips only)**: Maximum `9999px` allowed strictly for inventory state indicators (e.g., "In Stock", "Out of Stock").

## Components

### Buttons
- **Primary Action (Confirm Order / Process Payment)**: Background `#0F172A`, text `#FFFFFF`, border `none`, border radius `4px`. Hover state: `#1E293B`. Active state: `#020617`. Focus ring: 2px solid `#2563EB` with 2px offset.
- **Interactive Action (Add Item, Apply Filter)**: Background `#2563EB`, text `#FFFFFF`. Hover state: `#1D4ED8`.
- **Secondary / Neutral Action (Print Receipt, Export CSV)**: Background `#FFFFFF`, text `#0F172A`, border `1px solid #CBD5E1`. Hover: `#F8FAFC`.
- **Destructive Action (Void Transaction, Delete Entry)**: Background `#FFFFFF`, text `#DC2626`, border `1px solid #FECACA`. Hover: `#FEF2F2`.
- **Sizing**: Default height `36px` (`padding: 0.5rem 0.875rem`); compact POS height `30px` (`padding: 0.25rem 0.625rem`).

### Input Fields & Search Bars
- **Style**: White fill (`#FFFFFF`), 1px solid `#CBD5E1`, 4px radius. Height `36px` (compact `30px`).
- **Focus**: Border `#2563EB`, box-shadow `0 0 0 1px #2563EB`.
- **Barcode / SKU Input**: Embedded leading search icon, monospaced font family (`JetBrains Mono`), right-aligned clear shortcut icon (`Esc`).
- **Disabled State**: `#F1F5F9` background, `#94A3B8` text, `#E2E8F0` border.

### Data Tables (Wholesale & POS Catalog)
- **Header**: `#F8FAFC` background, 1px solid bottom border `#CBD5E1`. Font: `Inter`, 11px uppercase, tracking `0.02em`, color `#475569`.
- **Row Heights**: 
  - Standard Admin: `44px`
  - Compact POS Mode: `34px`
- **Cell Padding**: `6px 12px`.
- **Numeric & Pricing Columns**: Right-aligned, formatted with `JetBrains Mono`.
- **Row Hover**: Background `#F8FAFC`. Selected row: `#EFF6FF` with a 2px left border accent of `#2563EB`.

### Status Badges & Chips
- **Structure**: 11px font size, medium weight, `2px 6px` padding, 4px corner radius.
- **Paid / Completed**: Background `#ECFDF5`, text `#065F46`, border `1px solid #A7F3D0`.
- **Pending / Wholesale Credit**: Background `#FFFBEB`, text `#92400E`, border `1px solid #FDE68A`.
- **Cancelled / Void**: Background `#FEF2F2`, text `#991B1B`, border `1px solid #FECACA`.
- **Draft / Inactive**: Background `#F1F5F9`, text `#475569`, border `1px solid #E2E8F0`.

### POS Register Cart Panel
- **Fixed Summary Drawer**: Clean white container framed with 1px border `#E2E8F0`.
- **Line Items**: Clean alternating layout with quantity modifiers (`+` / `-`), line total set in `JetBrains Mono` (`13px`, semi-bold).
- **Payment Grand Total**: Pinned footer section with light tint `#F8FAFC`, grand total rendered in `display-lg` (`32px`) using `JetBrains Mono` to prevent misread transactions.

### Authentication & Gate Panels
- **Layout**: Centered minimal block, max-width `400px`.
- **Card**: Plain `#FFFFFF` surface, 1px border `#E2E8F0`, 6px radius. No drop shadow.
- **Form Controls**: Full-width inputs, high-contrast labels, primary full-width authentication submit button.