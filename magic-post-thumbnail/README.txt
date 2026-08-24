=== Generate Images (AI) - Magic Post Thumbnail ===
Plugin Name:       Generate Images (AI) - Magic Post Thumbnail
Version:           7.0.1
Tags:              featured image, ai image, image generator, thumbnail, bulk
Donate link:       https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=contact%40magic-post-thumbnail.com&item_name=Donation+for+Magic+Post+Thumbnail&currency_code=EUR&source=url
Contributors:      Mcurly, wondercode
Author URI:        https://magic-post-thumbnail.com/
Author:            Magic Post Thumbnail
Requires at least: 6.0
Tested up to:      7.1 
Stable tag:        7.0.1
Requires PHP:      7.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generate featured images from Bing, GPT Image, Unsplash and more. Automatic, bulk or Gutenberg — based on your post title.

== Description ==

**Magic Post Thumbnail generates images for your posts** — as featured images or inside the content — from image sources and AI models.

Free searches can use the **post title** or text analysis. Pro adds tags, categories, custom fields, custom requests and the OpenAI keyword extractor.

You pick a **fallback chain of image sources**: if the first source fails, the next one is tried.

**Photo sources:** Bing Images, Pixabay, Openverse, Flickr, Unsplash, Pexels, YouTube.

**AI sources:** GPT Image, Stable Diffusion, Replicate, Ideogram, Flux 2, Gemini (Nano Banana), Grok. Optional **style presets** (photorealistic, cinematic, “no text”…) apply to AI sources only.

You can also use the **first image already in the post**, or a **default fallback image**, and optionally draw a **title / logo bar** on the generated file (off by default).

Generate from the post editor, the **posts list column**, **bulk actions**, Gutenberg, or (Pro) cron and imports.

**<a target="_blank" href="https://magic-post-thumbnail.com/">Official Website</a>**
**<a target="_blank" href="https://magic-post-thumbnail.com/docs/">Documentation</a>**

https://www.youtube.com/watch?v=eNb5MXg5e4I

== What is included ? ==

= Magic Post Thumbnail for FREE =

<ul>
<li>Featured images for posts, pages and custom post types</li>
<li>Insert an image anywhere in the post content (position up to the 10th occurrence)</li>
<li>Search based on the title or text analysis</li>
<li>Gutenberg block to search and insert images</li>
<li>Image sources: Bing Images, GPT Image, Google Image API, Pixabay, Openverse, Flickr</li>
<li>Local sources: first image in the post, default fallback image</li>
<li>AI style presets and “no text” (when generating with an AI source)</li>
<li>Optional title / logo overlay on generated images</li>
<li>Generate or regenerate from the posts list</li>
<li>Bulk generation for posts or taxonomies</li>
<li>Dashboard health check and one-click test generation</li>
</ul>

= Magic Post Thumbnail PRO =

Upgrade to Pro for more image sources, automation and integrations.

<ul>
<li>Customisable <strong>crons</strong></li>
<li>Several image locations per generation</li>
<li>Image sources: Unsplash, Pexels, <strong>Stable Diffusion</strong>, <strong>Replicate</strong>, <strong>Ideogram</strong>, <strong>Flux 2</strong>, <strong>Gemini (Nano Banana)</strong>, <strong>Grok</strong></li>
<li>More results in the Gutenberg block</li>
<li>Search based on tags, categories, paragraph analyser, custom fields, custom request and <strong>OpenAI keyword extractor</strong></li>
<li>Image modifications: flip horizontally and/or crop by 10%</li>
<li><strong>Compatibility</strong> with REST, WPeMatico, FeedWordPress, WP All Import, Featured Image from URL, CMB2, ACF and Meta Box</li>
<li>Restricted domains and proxy</li>
<li><strong>Priority support with replies within 24 hours</strong></li>
</ul>


**<a target="_blank" href="https://www.youtube.com/watch?v=crI3V-Kkb8k">Youtube Tutorial</a>**

== Translations ==
* English
* French
* Spanish
* Russian
* Portuguese (Brazil)
* Portuguese (Portugal)
* Italian
* Japanese
* German
* Dutch
* Polish
* Turkish
* Vietnamese
* Chinese (Simplified)
* Chinese (Traditional)
* Korean
* Arabic
* Indonesian
* Ukrainian

== Screenshots ==
1. Bulk generation
2. Gutenberg block
3. Automatic settings
4. Post-processing (overlay, flip, crop)
5. Enable generation per post type
6. Source: fallback chain, API keys and preview
7. Generate featured images for a taxonomy
8. Generate or regenerate from the posts list
9. Cron settings (Pro)

== Support the plugin ==
If you've found the plugin useful, please consider <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=contact%40magic-post-thumbnail.com&item_name=Donation+for+Magic+Post+Thumbnail&currency_code=EUR&source=url">making a donation</a>. Thank you for your support !


== Installation ==
1. Activate the plugin.
2. Open **Magic Post Thumbnail**. The dashboard shows whether your chain and API keys are ready; you can generate a test draft there.
3. Under **Source**, build the fallback chain (drag to set priority) and enter API keys. Under **Automatic**, choose post types and image placement.
4. In a post, use **Click to generate** (Gutenberg or Classic), or generate from the posts list column.
5. For many posts at once: select them in the list and choose **Generate featured images** under Bulk Actions.

API setup guides: <a target="_blank" href="https://magic-post-thumbnail.com/docs-category/image-databases/">image databases</a>.


== Frequently Asked Questions ==

= How to generate images ? =

Several ways:
<ul>
<li>The **Click to generate** button on a post (Gutenberg and Classic Editor).</li>
<li>The **Generate / Regenerate** button in the posts list.</li>
<li>Bulk Actions → **Generate featured images**, for selected posts or a taxonomy.</li>
<li>The Gutenberg block, to search an image source and insert or set a featured image.</li>
<li>Pro: scheduled crons, or compatibility with REST, WPeMatico, FeedWordPress and WP Automatic.</li>
</ul>

= Is it unlimited ? =

Yes, as far as the plugin is concerned. Image sources and AI providers apply their own quotas and billing.

= Why images aren't generated anymore ? =

Heavy use of **Bing Images** in a short time can get your server temporarily blocked. Enable an interval between generations.

Bing Images is the default free photo source. Google HTML scraping was removed in 7.0.0; existing scraping settings are migrated automatically.

= Do AI sources use the style setting? =

Yes. Style presets and “no text” apply only to AI sources (GPT Image, Stability, Replicate, Ideogram, Flux 2, Gemini, Grok). Bing, Pexels and other photo sources ignore them.

= I have other pre-sale questions, can you help? =

Yes! You can ask us any question through our <a href="https://magic-post-thumbnail.com/contact-us/">support page</a>.


== Upgrade Notice ==

Upgrade to **<a target="_blank" href="https://magic-post-thumbnail.com/pricing/">Pro</a>** for advanced AI models, scheduled generation, additional image sources and integrations.


== Changelog ==

= 7.0.1 - August 24, 2026 =
* Fix generation aborting when Google Translate is rate-limited
* Switch to the current Google Translate API, with MyMemory as fallback

= 7.0.0 - August 15, 2026 =
* Bing Images replaces Google scraping (settings migrated automatically)
* Fix Bing Images returning unrelated results
* GPT Image replaces DALL·E 3 (API keys migrated automatically)
* PRO: Add Ideogram, Flux 2, Gemini (Nano Banana) and Grok; update Stability AI
* Redesign Source: chain on the left, bank settings and preview on the right
* Gutenberg search banks are now chosen on the Gutenberg Block page
* Regenerating an image now picks a different result
* AI style presets and “no text” option (AI banks only)
* New chain banks: first image in the post, and a default fallback image
* Generate or regenerate from the posts list column
* Optional title/logo overlay on generated images (off by default)
* Dashboard: configuration health check and a one-click test generation
* Warn before leaving unsaved settings; prevent duplicate generations
* Security hardening for AJAX, logs and image downloads
* REST auto-generation runs in the background
* Text domain aligned with the plugin slug (`magic-post-thumbnail`)
* Add translations (RU, PT, IT, JA, DE, NL, PL, TR, VI, ZH, KO, AR, ID, UK, ES)

= 6.2.2 - August 1, 2026 =
* PRO: Add support for OpenAI GPT Image 2 model (Replicate)
* PRO: Add Quality (auto/high/medium/low) and Moderation (auto/low) options for GPT Image 2
* PRO: Aspect ratio and output format now applied correctly for GPT Image 2

= 6.2.1 - April 2, 2026 =
* Bing Images is the default source for the Google Image (scraping) bank. The old Google HTML pipeline is off.
* Same search options map to Bing (`qft` filters). Filter: `mpt_google_scraping_bing_qft_tokens`.

= 6.2.0 - March 29, 2026 =
* Google Image (scraping): new way to fetch results; still unstable on many hosts.
* Bing fallback when Google fails: on by default; off in Banks or filter.

= 6.1.8 - January 29, 2026 =
* Fix Gutenberg Block: loader no longer stays infinite when image download fails
* Fix Gutenberg Block: improve download reliability for Openverse and Flickr images
* Fix Gutenberg Block: preserve URL-encoded characters in image URLs (fixes "Not Found" errors)
* Add HTTP timeout extension for large image downloads
* Add User-Agent header to prevent blocking by some image hosts
* Add user notification (snackbar) when image download fails

= 6.1.7 - November 21, 2025 =
* Fix dashboard footer

= 6.1.6 - November 13, 2025 =
* PRO: Improve compatibility with "Featured Image from URL" (FIFU) plugin - supports both free and premium versions
* PRO: Disable Envato Elements integration (API no longer working)
* Remove Envato Elements from available image banks in settings
* Update Freemius 2.13.0

= 6.1.5 - November 11, 2025 = 
* Add Black Friday discount

= 6.1.4 - October 16, 2025 =
* PRO: Add support for ByteDance Seedream-4 model (Replicate)
* PRO: Add support for Google Imagen models (Replicate) (imagen-4, imagen-4-fast, imagen-4-ultra, nano-banana)
* PRO: Improve dimension calculation for different aspect ratios (16:9, 4:3, 1:1, 3:2, 9:16) with imagen & seedream

= 6.1.3 - August 07 2025 =
* Allow image positioning up to the 10th occurrence (First to Tenth, plus Last) for inline content placement.
* Update Freemius 2.12.1

= 6.1.2 - June 24, 2025 =
* Fix bug with plugin Kadence Blocks
* Update Freemius 2.12.0

= 6.1.1 - May 07, 2025 =
* Add discount code for may
* PRO: Add cron interval to every 3 minute

= 6.1.0 - April 22, 2025 =
* Improve images of Dalle generation
* PRO: Add Replicate Api models with 42 images models.
* PRO: Add "Stable Image Ultra" as model for better quality

= 6.0.8 - April 16, 2025 =
* PRO: Fix "custom field" option
* PRO: Add post status choice for crons

= 6.0.7 - April 08, 2025 =
* Update compatibility with theme "cocoon" (japanese made theme)

= 6.0.6 - January 27, 2025 =
* Update freemius 2.11

= 6.0.5 - January 17, 2025 =
* Update scroll during generation
* Limit manual_search.js only to edit pages
* Add "Image reuse" option: Check for existing images media before downloading
* PRO: Fix WP All Import for all "Search Based on" options. Generated post-import

= 6.0.4 - December 17, 2024 =
* Update freemius 2.10.1
* PRO: Better compatibility with image galleries for "Meta Box" (metabox.io)

= 6.0.3 - November 27, 2024 =
* Add second image source for "image location" blocks

= 6.0.2 - November 18, 2024 =
* Add Black Friday discount
* PRO: Fix problems with REST Requests

= 6.0.1 - November 11, 2024 =
* Update CSS for delete button block
* Update Youtube Tutorial video link
* Fix migration problem with 6.0.0: could not generate image when plugin updated

= 6.0.0 - November 7, 2024 =
* PRO: Change automatic settings: Ability to add multiple images during generation
* PRO: Add Compatibility with CMB2, ACF & Meta Box (metabox.io)
* PRO: Add Image bank "Stable Diffusion"
* PRO: Strict Search Mode: include quotes inside the search term
* PRO: Add text analyzer per paragraph
* Add images inside content: Previously in paid version, now free.
* Add "random" image selection: Previously in paid version, now free.
* Add caption on images to mention author
* Improve "Custom Request" ("based on" option) by including taxonomies
* Improve text analyzer included with the plugin ("based on" option)
* "Inside content" option : add "div" and "a" as possible tags
* Add submenus into the sidebar
* Other minor code improvements
* Update Freemius version

= 5.2.11 - October 03, 2024 =
* Update Freemius version
* PRO: Add "Category Level" option for "Based on Custom Request"
* Add "Rights" tab options to allow roles to access the plugin dashboard

= 5.2.10 - August 12, 2024 =
* Fix security problem (level of privilege required)
* Update Freemius version

= 5.2.9 - July 22, 2024 =
* Update with WordPress 6.6 : Fix buttons for generating with Gutemberg editor

= 5.2.8 - July 13, 2024 =
* PRO: Fix Obsolete "Optional parameter" error
* Fix security problem (level of privilege required)

= 5.2.7 - June 04, 2024 =
* Fix bugs with Hook "Save Post" & "WP Insert Post"

= 5.2.6 - June 03, 2024 =
* Change display options with "enable alt"
* Add rating plugin notice

= 5.2.5 - May 22, 2024 =
* PRO: gpt-4o as model for "OpenAI Keyword Extractor". Cheaper/better quality.

= 5.2.4 - May 20, 2024 =
* Update for discount
* Update Freemius version

= 5.2.3 - April 17, 2024 =
* PRO: Fix problem with WP All import Compatibility
* PRO: Improve "Inside Content" when "Classic" block is inside Gutenberg

= 5.2.2 - April 09, 2024 =
* Fix bug with no result when post is not saved
* Update Freemius version

= 5.2.1 - April 04, 2024 =
* Changed Gutemberg detection method due to WP 6.5
* Remove warnings with option "enable alt"

= 5.2.0 - March 18, 2024 =
* Update Bulk page if no generation
* PRO: Manage image position anywhere in content

= 5.1.1 - January 15, 2024 =
* Update Freemius version
* Add post type selection for Hooks
* Fix CSS admin dashboard on small desktop resolutions

= 5.1 - December 20, 2023 =
* PRO: Add alt on images with translation possibility
* PRO: Add "Pro Account" link

= 5.0.4 - December 18, 2023 =
* Add CSS links underline for source settings
* Update jQuery UI version for better compatibility
* Update freemius version

= 5.0.3 - November 25, 2023 =
* Improve click area on button for manual generation
* PRO: Add "WP Insert Post" Hook. Also works with XML RPC requests

= 5.0.2 - November 17, 2023 =
* Update for Black Friday

= 5.0.1 - November 15, 2023 =
* Fix problem with automatic generation failing
* Remove the Gutenberg Block description in the settings. Wasn't the correct description
* Fix Dall-e sentence CSS problem

= 5.0.0 - November 14, 2023 =
* Change some Settings Pages
* Warning errors removed by removing useless include_once for free version
* Add multiple sources for automatic generation: select multiple Banks and set priorities
* New Gutenberg block to manually add images from multiple Image Banks
* Button for featured Image to select manually images from multiple Image Banks
* Remove Image Banks "Shutterstock" & "Getty Images", not very usefull
* Add API tester for settings to check API key validity
* Change Dall-e v2 to Dall-e v3