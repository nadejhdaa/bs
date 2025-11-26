# KC theme theme

[Bootstrap 5](https://www.drupal.org/project/bootstrap5) subtheme.

## Development.

### CSS compilation.

Prerequisites: install [sass](https://sass-lang.com/install).

To compile, run from subtheme directory: `sass scss/style.scss css/style.css && sass scss/ck5style.scss css/ck5style.css`


sass --watch scss/style.scss css/style.css && sass --watch  scss/ck5style.scss css/ck5style.css


sass --watch scss/style.scss:css/style.css components/main_slider/main_slider.scss:components/main_slider/main_slider.css components/main_slider_item/main_slider_item.scss:components/main_slider_item/main_slider_item.css


npm install -g sass


sass --watch scss/style.scss:css/style.css components/product_small_inline/product_small_inline.scss:components/product_small_inline/product_small_inline.css components/action_card/action_card.scss:components/action_card/action_card.css components/main_slider/main_slider.scss:components/main_slider/main_slider.css components/main_slider_item/main_slider_item.scss:components/main_slider_item/main_slider_item.css components/bg_img_and_link_medium/bg_img_and_link_medium.scss:components/bg_img_and_link_medium/bg_img_and_link_medium.css components/slider_items/slider_items.scss:components/slider_items/slider_items.css components/product_full/product_full.scss:components/product_full/product_full.css 

sass --watch scss/style.scss:css/style.css components/bg_img_and_link_medium/bg_img_and_link_medium.scss:components/bg_img_and_link_medium/bg_img_and_link_medium.css
