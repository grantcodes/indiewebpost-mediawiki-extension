# IndieWeb Posts MediaWiki Extension

This is an extension to render indieweb friendly posts in a variety of formats

## Usage

### Basic

  {{#indiewebpost:html=<div class="h-entry">
    <div class="e-content">
      <p>A super basic note</p>
    </div>
  </div>}}

You must include html for the post you want to display.

By default the extension will show the html you provided, parsed mf2 JSON, example micropub JSON (just slightly simplified mf2 JSON), and the rendered html using the default wiki styles

### Adding a Screenshot

You can add a screenshot with an example of the post with improved styles, as well as an optional title for the screenshot:

  {{#indiewebpost:html=<div class="h-entry">
    <div class="e-content">
      <p>A super basic note</p>
    </div>
  </div>
  |screenshot=http://example.com/screenshot.jpg
  |screenshot-title=The example screenshot}}

### Hiding Tabs

You can hide tabs using the `hide-tabs` param and adding a comma seperated list of the tabs you want to hide

Available options are `html`, `mf2`, `micropub` and `rendered`.

  {{#indiewebpost:html=<div class="h-entry">
    <div class="e-content">
      <p>A super basic note</p>
    </div>
  </div>
  |hide-tabs=rendered,micropub}

### Replacing Micropub JSON

Since a micropub request can be different to the parsed mf2 there is the option to overwrite it with your own JSON.

Just be careful with MediaWiki formatting, as it can cause some issues...

  {{#indiewebpost:html=<div class="h-entry">
    <div class="e-content">
      <p>A super basic note</p>
    </div>
  </div>
  |micropub={
    "type": ["h-entry"],
    "properties": {
      "content": ["micropub content"]
    }
  }
  }}