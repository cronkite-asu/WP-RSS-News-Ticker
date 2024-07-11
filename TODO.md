# RSS News Ticker

### Todo

- [ ] Save RSS Items as custom post type.
- [ ] Cache Feed.
  - [x] Use tranisents to cache API calls.
  - [ ] Check if feed is updated with etag header
- [ ] Add capablity to manage page tabs.
- [ ] Add optional local feeds from news post types.
- [ ] Cleanup settings on removal of plugin.
- [ ] Move registering hooks out of `__construct` methods
- [ ] add handlers for different API response codes/errors
- [ ] Retrieve default options with `get_option` function
- [ ] Make option to select input type on custom feed page
- [ ] Add reset button to custom feed page
- [ ] Allow inport of csv file for custom feed page

### In Progress

- [ ] Validation during WordPress santiation callback.

### Done ✓

- [x ] Validate path input for url field in config page.
  - [x] Javascript Validation.
  - [x] HTML5 Validation.
- [x] Make AP News feed optional.
- [x] Remove school news from AP config page.
- [x] Move configuration page to be a submenu of Settings main menu.
- [x] Create TODO.md
