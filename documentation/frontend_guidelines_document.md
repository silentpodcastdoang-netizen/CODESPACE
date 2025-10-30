# Frontend Guidelines for CODESPACE

This document describes how to build and maintain the web-based user interface for the CODESPACE project scaffolding tool. Anyone reading this—whether you’ve built web apps before or not—will understand the overall setup, why we chose each piece, and how it all fits together.

## 1. Frontend Architecture

**Overall Structure**
- We use React (with TypeScript) as our main framework. It powers reusable UI components and keeps our code organized.
- Vite is our build tool: it gives fast startup, instant hot-reload, and optimized production bundles.
- We organize code in two top-level folders:
  - `src/components/` for shared building blocks (buttons, inputs, modals).
  - `src/pages/` for the main screens in the wizard (project info, template selection, final summary).

**Scalability, Maintainability & Performance**
- Component-based: small, focused pieces make it easy to add or change features as the product grows.
- Code splitting: each wizard step is lazy-loaded so users only download what they need when they click forward.
- Clear folder structure: as we add more pages or new features (like plugin settings), we simply create a new folder under `pages` or `components`, keeping related files together.

## 2. Design Principles

**Usability**
- Keep interactions simple: one form per step, clear “Next” and “Back” buttons.
- Inline help text under each input to guide users without digging through docs.

**Accessibility**
- All form fields are labeled with HTML `<label>` elements.
- Keyboard navigation: users can tab through fields and activate buttons with Enter or Space.
- Color contrast meets WCAG AA standards.

**Responsiveness**
- Mobile-first layout: the wizard steps stack vertically on small screens.
- On wider screens, the form sits centered in a card with a progress sidebar showing where you are.

## 3. Styling and Theming

**Styling Approach**
- We use Tailwind CSS for utility-first styling. It lets us apply spacing, colors, and typography with simple class names.
- Custom components have a small set of reusable class names; we avoid inline styles.

**Theming**
- A single `tailwind.config.js` file defines our color palette and font stack.
- If we ever need dark mode or custom branding, we just extend Tailwind’s theme and switch classes at runtime.

**Visual Style**
- Style: Flat, modern design with subtle shadows on cards to separate sections.
- Glassmorphism touches on modals: translucent panel background with a slight blur.

**Color Palette**
- Primary: #6200EE (deep purple)
- Secondary: #03DAC6 (teal)
- Background: #F5F5F5 (light gray)
- Surface (cards): #FFFFFF (white)
- Text (dark): #212121
- Text (light): #FFFFFF

**Font**
- We use “Inter” (sans-serif). It’s clean, highly legible, and pairs well with our modern look.

## 4. Component Structure

**Organization**
- Atomic Design approach: 
  - `atoms/`: buttons, inputs, icons
  - `molecules/`: form groups, dropdowns
  - `organisms/`: header, wizard step container, footer

**Reusability**
- Each component lives in its own folder with three files:  
  - `Component.tsx` (React code)  
  - `Component.test.tsx` (unit tests)  
  - `Component.module.css` or Tailwind classes in the `.tsx` (styling)
- We avoid copying and pasting. If a form field or button appears in multiple places, it’s a shared atom.

## 5. State Management

**Approach**
- We use React Context and the built-in `useReducer` hook to store user inputs across steps.
- The context lives at the top level in `src/context/ProjectWizardContext.tsx`.

**Data Flow**
1. User fills out a form in Step 1; component dispatches an action: `{ type: 'SET_PROJECT_NAME', payload: 'MyApp' }`.
2. The reducer updates the state and makes it available to any other step that needs it.
3. On final review, we collect the entire state and send it to the API or back to the CLI.

## 6. Routing and Navigation

**Library**
- We use React Router v6.

**Structure**
- Each wizard step has its own route: 
  - `/step/1` → Project Details
  - `/step/2` → Template Selection
  - `/step/3` → Advanced Options
  - `/step/4` → Summary & Generate

**Navigation**
- “Next” and “Back” buttons use `useNavigate()` to move between routes.
- We guard routes: if a user tries to jump ahead without filling required fields, we redirect them back to the missing step.

## 7. Performance Optimization

- **Lazy Loading**: use React’s `lazy()` and `Suspense` to load each step component only when needed.
- **Code Splitting**: Vite automatically splits vendor code from our own code, so users download React and utility libraries once.
- **Asset Optimization**: SVG icons are inlined; images (if any) are compressed via build-time plugins.
- **Cache Headers**: static assets served with long-term caching and hashed filenames.

These strategies mean the wizard loads in under 200KB on first visit and feels snappy as you click through.

## 8. Testing and Quality Assurance

**Unit Tests**
- Jest + React Testing Library for components.
- Each atom and molecule has at least one test to verify rendering, user events, and state updates.

**Integration Tests**
- We write a few tests that mount multiple components (e.g., a full form group) to ensure they work together.

**End-to-End (E2E) Tests**
- Cypress tests simulate a user going through the entire wizard: 
  - Fill in fields, click Next/Back, verify final summary, and mock the generation API call.

**Linting & Formatting**
- ESLint with a shared config enforces code style and catches errors early.
- Prettier automatically formats code on save so everyone’s code looks the same.

## 9. Conclusion and Overall Frontend Summary

These guidelines lay out everything you need to know to add features, fix bugs, or style the CODESPACE web interface:
- A clear React + TypeScript + Vite architecture supports fast iteration and long-term growth.
- Design principles ensure the wizard is easy to use, accessible on any device, and visually consistent.
- Tailwind CSS and a well-defined theme keep styling both powerful and straightforward.
- Component-based organization and Context + Reducer state management make the code easy to maintain.
- Routing, lazy loading, and testing strategies guarantee that users have a smooth experience and that we catch issues quickly.

With these rules in place, any developer—new or experienced—can jump in and confidently contribute to the frontend of CODESPACE. Happy coding!