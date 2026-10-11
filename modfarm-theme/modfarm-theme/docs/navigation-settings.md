# Shared navigation

Open ModFarm Settings → Navigation. Select menus by name, choose a simple or split layout, branding, alignment, search, mobile presentation, descriptions and image sizes. Enable **Use shared header navigation** and/or **Use shared footer navigation**, then save. Existing Nav blocks consume the matching profile dynamically across singular content, archives, template parts, and custom patterns. No PPB rebuild is needed. A header/footer must contain a ModFarm Nav block; the profile does not inject one into layouts without navigation.

Profiles start disabled to preserve existing sites. The footer profile defaults to centered, expanded mobile links with search hidden. Footer rendering omits branding. Header and footer styles, typography, logo/icon sizing and mobile colors are in the same tab. Menu items remain managed through Appearance → Menus.

For an exception, select the Nav block and choose **Use this block’s settings** under Navigation configuration. Its existing layout/menu/mobile/search controls then apply. Local style overrides remain a separate switch. Returning to shared settings restores the current site profile. Disabling a shared profile restores the saved block attributes; no content is rewritten.

With JavaScript-enhanced mobile navigation active (up to 1024px), the standalone search icon is hidden and search remains in the mobile menu. Expanded navigation retains its search icon. Without JavaScript, links and native search remain accessible. Centered footer menus center both expanded links and their mobile toggle.
