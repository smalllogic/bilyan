# Bilyan WordPress 主题精讲与维护手册

版本：1.4.1  
适用主题：`bilyan`  
适用 WordPress：6.4 及以上  
适用 PHP：8.0 及以上

## 1. 这个主题到底是什么

Bilyan 是一个独立的 WordPress 商店目录主题。它的定位不是完整的电商结算系统，而是一个“产品展示 + 购物车 + 订单请求”的市场平台。顾客可以浏览产品、按照分类查找、搜索产品、查看详情、选择数量并提交订单请求；网站不会在前端收款，也不会接入信用卡、PayPal 或其他支付网关。订单会进入 WordPress 后台的 Orders，并尝试通过 `wp_mail()` 通知 `hellobilyan@gmail.com`。

这个取舍很重要。主题负责商品内容、分类、图片、价格快照、订单联系人和请求流程；库存扣减、自动税费、配送计价、付款确认、退款、会员账户和物流追踪都没有实现。它适合早期市场、人工确认价格、线下付款、WhatsApp/电话跟进或暂时不需要支付功能的商店。如果将来需要真正的支付与库存系统，应该接入 WooCommerce、SureCart 或专门的订单插件，而不是继续把所有功能堆进主题。

主题的视觉系统来自你提供的 `logo.jpg`：深红色是主要行动色，暖白和米色负责背景，暗棕色负责文字。产品页、Shop 页、分类页和购物车使用同一套颜色变量，避免每个页面看起来像不同的网站。

## 2. 文件结构和每个文件的职责

主题根目录位于 `bilyan/`，安装包是同级的 `bilyan.zip`。WordPress 上传时必须上传 ZIP，解压后目录结构应该是 `wp-content/themes/bilyan/`，不能多套一层 `bilyan/bilyan/`。

核心 PHP 文件的作用如下：

- `style.css` 是主题识别文件，也包含版本号和基础声明。它还作为全局样式文件的入口说明。
- `functions.php` 是主题启动文件。它加载 `inc/catalog.php`、`inc/orders.php`、`inc/setup.php`，注册主题支持、菜单、CSS、JavaScript，以及全站共用的帮助函数。
- `header.php` 输出顶部地址栏、Logo、搜索框、购物车入口、菜单和 `<main>` 开始标签。
- `footer.php` 输出页脚、购物车弹窗、toast 容器，并调用 `wp_footer()`。删除 `wp_footer()` 会造成很多插件脚本无法加载。
- `front-page.php` 是首页。它包含 Hero、优势说明、带图片的分类区域、推荐商品、品牌说明和下单步骤。
- `archive-bilyan_product.php` 是 Shop 页、搜索结果和产品分类归档共用的产品列表模板。它根据 `is_tax()`、`is_search()` 区分普通 Shop、分类页和搜索页。
- `taxonomy-bilyan_category.php` 只是把分类请求交给产品归档模板，因此分类页的布局和 Shop 保持一致。
- `single-bilyan_product.php` 是产品详情页，包含大图、价格、库存状态、数量、加购物车、详情、订购步骤、问答和相关产品。
- `page.php` 处理 Cart、About、Contact、FAQ、Privacy、Ordering & delivery 等普通页面。它根据页面 slug 输出专用布局。
- `index.php` 是普通文章或没有更具体模板时的兜底模板，`404.php` 是不存在页面的错误页。

功能文件的作用如下：

- `inc/catalog.php` 注册 Products、Product categories、FAQs 和 Orders 四种内容类型，并处理分类图片、产品字段、产品列表分页和排序。
- `inc/orders.php` 处理 Ajax 购物车核验、订单提交、订单后台详情、邮件通知、防重复提交和频率限制。
- `inc/setup.php` 在启用主题后创建页面、生成默认 FAQ、提供 Bilyan setup 设置页和示例产品导入。

资源文件的作用如下：

- `assets/store.css` 是全站基础样式和响应式样式。它采用 CSS 变量控制红色、背景、文字和边框颜色。
- `assets/product.css` 只在产品详情页加载，避免把详情页的复杂样式加载到每个页面。
- `assets/home.css` 只在首页加载，用于首页图片分类卡片的布局和移动端适配。
- `assets/store.js` 负责 localStorage 购物车、加减数量、购物车页面 Ajax、订单提交、手机菜单和加入购物车弹窗。
- `assets/images/` 存放原始 Logo、示例商品 SVG、分类可复用的 SVG 和占位图。SVG 是代码内置的本地图片，不依赖外部 CDN。

## 3. WordPress 启动顺序和主题思想

主题不是一个单独运行的 PHP 网站，它运行在 WordPress 的生命周期中。WordPress 加载主题后，`functions.php` 先被读取，然后各种 `add_action()` 和 `add_filter()` 把函数挂到 WordPress 的事件上。真正的注册和输出在合适的事件发生时执行。

`after_setup_theme` 用于注册 `title-tag`、文章缩略图、HTML5 支持、自定义 Logo 和 Primary navigation。这里不能太早调用依赖 WordPress 查询的内容，否则可能在主题环境还没准备好时出错。

`init` 用于注册自定义文章类型和分类法。`register_post_type()` 的第一个参数是内部 key，不能随意改名，因为数据库中保存的是这个 key。Bilyan 使用：

- `bilyan_product`：公开产品，归档地址是 `/shop/`，详情地址默认是 `/product/slug/`。
- `bilyan_category`：层级分类，类似 WordPress 原生分类，可拥有父级分类。
- `bilyan_faq`：后台可编辑 FAQ，但不直接作为公开文章访问，FAQ 页面负责读取它。
- `bilyan_order`：私有订单，访客不能通过前台和 REST API 读取。

把 Products 注册在主题里很方便，因为上传主题即可工作；但 WordPress 官方更推荐站点核心内容类型放在插件里。原因是切换主题后主题注册的类型会暂时消失，产品数据虽然还在数据库，后台入口和 URL 规则却不再注册。对于长期运营的网站，可以把 `catalog.php` 和 `orders.php` 拆成一个 Bilyan Core 插件，主题只保留展示层。

主题启用时调用 `flush_rewrite_rules()`，确保 `/shop/`、`/product/` 和分类 URL 能工作。这个操作只能在主题启用时做，不能每次请求都刷新，否则会明显增加数据库和文件写入压力。

## 4. 产品模型和后台操作

添加产品时，标题就是产品名称；Excerpt 是列表页和详情页顶部的简介；正文是详情页“产品介绍”；Featured image 是商品主图；分类由 Product categories 分配。

产品额外字段保存在 post meta：

- `_bilyan_price`：价格字符串，保存时会检查数字、非负数和最大值，并格式化为两位小数。
- `_bilyan_sku`：产品代码，便于后台和邮件里识别。
- `_bilyan_unavailable`：值为 `1` 时产品不可下单，前端按钮禁用，服务器也会拒绝。
- `_bilyan_featured`：值为 `1` 时产品优先显示在首页推荐区域。
- `_bilyan_demo_image`：示例商品使用的本地 SVG 名称。正式产品应上传 Featured image。

价格可以留空。留空后前台不输出价格占位文字，但产品仍然能加入购物车；订单中的该商品会被标记为需要人工确认价格，邮件正文也不会伪造金额。这个行为比显示错误价格更安全。

产品卡片调用 `bilyan_product_card()`，因此首页、Shop、相关产品和搜索结果都使用统一的卡片结构。修改卡片时优先改这个函数，不要复制四五份 HTML，否则不同页面会很快产生视觉和逻辑差异。

## 5. 分类模型、分类图片和 Shop 页

分类是 `bilyan_category` taxonomy。它支持父子层级、公开归档和后台产品数量。每个分类可以在编辑表单中填写 `Category image URL`，地址保存到 term meta `_bilyan_category_image`。主题会验证 URL 是合法地址，前台输出时再使用 `esc_url()`。

如果没有自定义图片，`bilyan_category_image()` 会根据分类名选择本地图片：Electronics 使用 headphones，Fashion 使用 bag，Home 使用 chair，Sports 使用 bottle，其他分类使用 lamp。这样新建分类后即使没有上传图片，Shop 和首页也不会出现空白区域。

进入 Shop 时，`archive-bilyan_product.php` 先输出图片分类卡片，再输出产品列表。普通 Shop 显示所有分类；分类归档显示分类头图、分类描述和该分类产品；搜索结果隐藏分类浏览区，避免搜索页面被无关内容推开。产品列表由 `pre_get_posts` 设置为每页 20 条，并根据 `sort` 参数支持最新、名称 A–Z 和最旧排序。

“每页 20 条”同时适用于 Shop、分类页和产品搜索。分页由 `the_posts_pagination()` 输出，URL 参数会由 WordPress 主查询处理。如果将来要增加价格筛选、品牌筛选或库存筛选，应使用 `pre_get_posts` 配合白名单参数，并避免直接把用户输入拼进 SQL。

## 6. FAQ：后台内容、常驻导航和显示方式

FAQ 现在是 `bilyan_faq` 自定义文章类型。后台进入 **FAQs → Add FAQ**，标题填写问题，编辑器填写答案，发布后进入 FAQ 页面。`page.php` 会用 `get_posts()` 按 `menu_order` 升序读取已发布 FAQ，并用 `wpautop()` 和 `wp_kses_post()` 输出答案。管理员可以使用页面属性中的顺序字段改变显示顺序。

主题在启用时以及第一次初始化时生成 7 条默认 FAQ。生成函数先检查是否已经有发布的 FAQ，避免重复插入。你可以直接修改这些默认条目，也可以删除后建立自己的 FAQ。因为 FAQ 是后台内容，未来不需要修改 PHP 就能改问题和答案。

导航分两层：没有自定义 Primary menu 时，`header.php` 输出主题默认菜单，包含 Home、Shop、About us、FAQ 和 Contact；如果后台指定了 Primary navigation，`wp_nav_menu_items` filter 会检查菜单中是否已经有 FAQ，没有时自动追加 FAQ。这样切换页面时 FAQ 链接不会因为模板不同而消失。需要注意，管理员如果把导航位置指定给另一个菜单位置，主题不会读取那个位置；应该在 **外观 → 菜单** 把菜单指定给 Primary navigation。

## 7. 购物车的完整前端流程

购物车不是 WordPress session，而是浏览器 localStorage。键名由站点 ID 和站点 URL 计算，因此不同站点不会共用同一个购物车。每条数据只保存产品 ID 和数量，例如：

```json
[{"id":123,"quantity":2},{"id":456,"quantity":1}]
```

加入按钮带有 `data-add`，产品详情页额外带 `data-quantity-input`。JavaScript 先读取 localStorage，过滤空值、负 ID、非整数数量、重复 ID 和超过 99 的数量，再合并相同产品。每个购物车最多 50 种不同产品。

加入成功后，`showCartModal()` 打开遮罩弹窗。弹窗从当前产品卡或产品详情页读取图片和标题，显示数量，并提供 Keep shopping 和 View cart。关闭方式包括关闭按钮、背景区域和 Escape 键。弹窗出现时给 body 增加 `modal-open`，防止背景滚动。

购物车页面加载后，JavaScript 把 localStorage 的数据 POST 到 `admin-ajax.php` 的 `bilyan_cart` action。服务器重新查询产品、可见状态、不可售标记和价格，再返回规范化商品行。客户端只展示服务器返回的名称、图片、URL、小计和总额。

购物车数量改变时会重新请求服务器。用户不能靠修改浏览器 JSON 把一个产品的价格改成 0，也不能把下架或草稿产品提交出去。

## 8. 订单流程、服务器核验和邮件

订单提交使用 `bilyan_order` Ajax action。购物车接口返回 `wp_create_nonce('bilyan_order')`，订单接口用 `check_ajax_referer()` 校验。Nonce 只证明请求来自近期站点页面，不能代替权限检查；本主题仍然在后台保存、编辑订单时检查管理员权限。

服务器会清理并验证姓名、邮箱、电话、地址、备注、同意隐私政策和隐藏 honeypot 字段。电话使用白名单正则，文本字段限制长度，邮箱使用 `sanitize_email()` 和 `is_email()`。

订单的关键流程如下：

1. 重新解析购物车，拒绝空购物车、无效数量、不可售、草稿和不存在产品。
2. 根据邮箱和前端生成的 request ID 计算 option key。
3. 用 `add_option()` 做原子占位，阻止同一请求并发创建两笔订单。
4. 用 `wp_insert_post()` 创建私有 `bilyan_order`。
5. 把客户资料、购物车、价格快照、同意时间和订单参考号保存到 `_bilyan_order`。
6. 发送邮件到设置页中的通知邮箱，并保存 `_bilyan_mail_sent` 状态。
7. 返回 `BLY-日期-订单 ID` 参考号。

订单先入库再发邮件，因此邮件服务器失败不会让订单消失。`wp_mail()` 返回成功只表示 WordPress 邮件层接受了发送，不代表邮件已经进入 Gmail 收件箱。正式上线前应配置 SMTP 插件并发送真实测试邮件。

订单个人资料保存在 WordPress 数据库中。管理员可以在 Orders 查看客户、地址、产品、数量、备注和邮件状态，并把状态改为 New、Contacted、Confirmed、Completed 或 Cancelled。订单类型 `publicly_queryable` 为 false、`show_in_rest` 为 false，访客不能直接访问。

## 9. 安全设计和仍然存在的风险

主题采用了多层保护：输出用 `esc_html()`、`esc_attr()`、`esc_url()`；正文答案使用 `wp_kses_post()`；后台保存产品和订单状态使用 nonce；管理员操作使用 `current_user_can()`；订单入口同时提供 nonce、字段验证、产品状态复核、重复请求锁和频率限制。

每个 IP 每小时最多创建 8 个新请求。这个限制使用 `hash_hmac()` 保护 transient key，避免直接把 IP 写进键名。它不是完整的 WAF，也不能替代 Cloudflare、主机防火墙和 SMTP 防垃圾配置。

主题没有实现登录、验证码、库存锁定或支付签名。恶意用户仍可以发送大量失败请求，虽然不会创建订单，但可能消耗 PHP 进程。因此生产环境建议开启缓存、主机级限流、Cloudflare Turnstile 或专业反垃圾插件。

分类图片允许填写外部 URL。为了稳定和隐私，建议把图片上传到 WordPress 媒体库后使用本站 URL，而不是使用陌生站点的图片地址。外部图片可能失效，也可能带来混合内容或追踪问题。

## 10. 设置、部署和升级

安装步骤是：后台 **外观 → 主题 → 安装主题 → 上传主题**，选择 `bilyan.zip` 并启用。启用后访问 **外观 → Bilyan setup**，确认订单通知邮箱、地址和货币标签。货币设置只改变显示标签，不做汇率换算。

正式部署前要做四件事：

- 导入或上传真实产品，替换示例商品的标题、价格、描述、SKU 和图片。
- 检查 Privacy 和 Ordering & delivery 页面，改成真实业务政策。
- 配置 SMTP 并发一封测试邮件。
- 到 **设置 → 固定链接** 保存一次，确保重写规则刷新。

升级时上传新版 ZIP 并选择替换当前主题。产品、分类、FAQ 和订单都在数据库中，不会因替换主题文件而删除。升级前仍然应备份数据库和 `wp-content/uploads`。如果切换到其他主题，数据仍在，但主题注册的 Products、FAQs 和 Orders 管理入口会暂时消失。

建议把产品和订单视为业务数据，把主题视为展示和流程代码。长期运营时可把 `inc/catalog.php`、`inc/orders.php` 和 FAQ 注册逻辑迁移到一个核心插件，让换主题不会影响数据类型。

## 11. 常见问题排查

**Shop 显示 404。** 进入固定链接设置并保存一次。检查主题是否真的启用，以及 `has_archive` 是否仍是 `shop`。如果服务器是 Nginx，确认 WordPress 的 `try_files` 重写规则正确。

**导航没有 FAQ。** 确认菜单被指定到 Primary navigation。检查 FAQ 页面 slug 是否仍是 `faq`。如果使用缓存插件，清理页面缓存和菜单缓存。主题默认菜单不依赖后台菜单，也会输出 FAQ。

**FAQ 页面为空。** 检查后台 FAQs 是否有发布状态的内容。主题第一次初始化会生成默认 FAQ；如果站点禁用了主题的 `init`，可手动新增一条 FAQ，页面会立即读取。

**商品图片不显示。** 检查 Featured image 是否设置，或示例商品的 `_bilyan_demo_image` 是否为允许的文件名。分类图片应使用可访问的 HTTPS 图片 URL。清理图片优化插件产生的缓存。

**加入购物车弹窗没有出现。** 检查浏览器是否启用 JavaScript，确认 `store.js` 已加载，并清理旧的缓存文件。购物车仍应保存到 localStorage；如果浏览器禁止 localStorage，主题会提示用户保持页面打开。

**订单没有收到邮件。** 先在 Orders 查看订单是否存在。如果订单存在而 Email notification 显示失败，配置 SMTP。如果显示已发送但收件箱没有邮件，检查 SPF、DKIM、DMARC、Gmail 垃圾箱和主机邮件日志。不要因为收件箱没看到邮件就重复创建订单。

**订单重复。** 同一页面请求使用 request ID 和 option 锁；刷新页面后再次填写会被视为新的请求。若业务需要更严格的幂等性，应增加客户邮箱、产品快照和时间窗口的业务去重规则。

**价格不正确。** 前端价格不是最终来源。订单接口会从产品 meta 重新读取价格。如果数据库价格正确但页面缓存旧，清理页面缓存和对象缓存。更改货币标签不会转换已有价格。

## 12. 可扩展方向

最值得优先扩展的是核心插件化、媒体库选择器、库存字段、订单状态通知和 SMTP 配置检测。产品查询可以增加品牌、价格区间和库存过滤，但应使用 `WP_Query` 参数白名单。订单可以增加后台批量状态、CSV 导出和客户确认邮件，不过任何新增邮件都应遵守隐私政策和退订要求。

如果接入 WooCommerce，应让 WooCommerce 接管价格、购物车、库存和支付，Bilyan 主题只负责视觉模板；不要同时让两套系统都处理同一笔订单。若需要多商家市场，还要增加商家实体、权限、佣金、分账和订单拆分，这已经超出当前主题的设计边界。

## 13. 当前验证记录

当前代码已经进行 PHP 语法检查、JavaScript 语法检查、8 项购物车前端逻辑测试、9 项产品模板测试和 46 项 WordPress 集成测试。集成测试覆盖页面生成、产品搜索、分类归档、404、服务端价格核验、数量限制、订单保存、重复请求、不可售商品、订单隐私、询价价格为空和频率限制。

当前环境没有可连接的真实浏览器，因此没有完成截图级的桌面和手机视觉回归测试。上线前建议使用实际手机和桌面浏览器各走一遍：首页分类、Shop 第 20/21 个产品、FAQ 导航、详情页加购物车、购物车数量、订单提交和邮件收件箱。

这份主题的基本维护原则是：内容在后台，结构在模板，流程在 Ajax，金额在服务器，邮件只做通知，支付和库存交给专门系统。理解这条边界，就能安全地继续修改 Bilyan，而不会因为一次视觉调整破坏订单数据。
