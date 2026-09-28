# Bilyan

Version 1.5.0 adds 75 native Customizer settings for brand colors, homepage media and copy, section visibility, About content and editor takeover, contact copy and footer text. Open Appearance → Customize → Bilyan · 品牌与静态内容. Logo and site icon remain under Site Identity. Uploaded logos are displayed without cropping. The new configuration suite is in verification/customize.php outside the installable theme.

Version 1.4.0 adds admin-managed FAQs that are always linked in the Primary navigation. Version 1.3.0 adds image-led category cards to the homepage. Version 1.2.0 added image-led category browsing to the Shop page and category archives, plus a centered add-to-cart modal with product image, quantity and cart actions. Version 1.1.0 refreshed the product detail layout with a larger image, a full-image link, a clearer purchase area, ordering guidance, expandable questions and category-related products. Products without prices show no price placeholder on detail pages or cards. Updating the installed theme preserves product and order records. The first administrator backend visit updates only the matching default ordering-policy sentence from version 1.0.

FAQs are managed in the WordPress admin under FAQs → Add FAQ. The title is the question and the editor is the answer. Published FAQs are rendered on the FAQ page in page-order order. The theme keeps an FAQ link in the Primary navigation across every page, including when a custom Primary menu is assigned.

Shop, category archives and product search show 20 products per page, then paginate automatically.

A self-contained WordPress catalog and order-request theme. No online payments and no WooCommerce dependency.

Install the `bilyan.zip` file through Appearance → Themes → Add New → Upload Theme, then activate. Requires WordPress 6.4+ and PHP 8.0+.

Theme activation creates Cart, About, Contact, FAQ, Privacy and Ordering & delivery pages without replacing existing pages at those paths. The homepage and Shop archive are supplied by the theme.

Use Appearance → Bilyan setup for the notification email, business address, currency label and optional sample catalog import. Sample products use bundled illustrations and are clearly described as demonstrations in their full descriptions. Replace or remove them before launch.

Use Products to manage product titles, full descriptions, excerpts, featured images and categories. Product information includes an optional price, SKU, availability toggle and homepage feature toggle. Empty prices are hidden on product pages and cards; customers can still add these products to their cart. USD is the default currency; changing the label does not convert prices.

Order requests are saved privately and shown under Orders for administrators. Notification mail is attempted after saving. Customers receive an on-screen reference; this theme does not send customer confirmation emails. Configure and test a working SMTP service before launch. A successful mail transport return is not proof of inbox delivery.

Review Privacy and Ordering & delivery content before launch. About, Contact and FAQ have built-in layouts; additional page-editor content is rendered after those layouts. About can alternatively use only its page-editor body through the Customizer toggle. Assign an optional menu to Primary navigation and optionally set a custom logo.

Products and orders are registered by this theme. Switching themes hides their management screens but does not delete stored data. Back up the database and media files before changing themes. Quantities are limited to 1–99 per product, with up to 50 products per cart. No inventory deduction, online payments, automated delivery pricing or customer accounts are provided.

Theme code and original SVG illustrations: GPL-2.0-or-later. The supplied Bilyan logo belongs to its respective owner. No externally loaded fonts, images or scripts are required.

Tested with WordPress 7.1.2 and PHP 8.5.7. Server integration and frontend logic checks passed; real-browser visual review remains to be performed.
