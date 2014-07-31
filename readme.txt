=== WP Fanfiction and Writing Archive Basic ===
Contributors: FandomEnt
Tags: longread, fanfiction, writing, education
Donate link: http://writing-archive.com/donate
Requires at least: 3.0
Tested up to: 3.9.1
Stable tag: 1.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Fanfiction and Writing Archive Basic turns Wordpress sites into online writing communities for fanfiction, longread, education, magazine, and more. 

== Description ==
WP Fanfiction and Writing Archive turns Wordpress sites into online writing communities for fanfiction, longread, education, magazine, and more. Writers can create single stories or books with chapters to share with readers. Powerful search, and terrific tools for both writers and readers. Turn a blog into an online writing community.

== Installation ==
Follow typical plugin installation: install and activate. 

Optional settings will be under 1) Fanfiction OPTIONS and 2) Fanfiction 

Dashboard > Fanfiction OPTIONS  > Fanfiction Options
Create Fan Fiction Page: Name the page you would like your archive of stories to display on. Suggestions:  fanfiction, stories, essays, poems, etc.
Override Dashboard: This is not required. You can choose your preference.
Enhanced Admin Interface:  Recommended to check this to turn it ON. This gives writers access to managing comments from their dashboard.
Default Role:  Leave UNchecked.

Fan Fiction Page Stylesheet: Do not alter.
Fan Fiction Scores: Check to ON if you will have scoring on your site.

Posting Page: This is the slug of the page where writers will post their writings. Example:  post-fanfiction  Example 2:  submit-story


Dashboard> Fanfiction OPTIONS  > Manage Scores
You do not need to do anything to this setting.

Dashboard > Fanfiction OPTIONS > Scores Options
Scores Image: Select the image  you wish to represent the scores.
Maximum number of scores:  This sets highest rating on the scale. 5 = a rating scale of 0 - 5.
Individual Scoring Text/Value: Determine what each image represents. Example a single star image = a rating of 1. 
Scores AJAX Style: Select your options for Loading Image, Show Fading In/Out of Scores, Who is Allowed to Score, and Score Logging.

Dashboard > Fanfiction OPTIONS > Fiction Scores Templates
We suggest you leave these settings as-is unless you completely understand the variables. 

Dashboard > Fanfiction > Fanfiction
No settings. This displays stories submitted and their stats. 



Dashboard > Fanfiction > Add Fanfiction
No settings. This displays the front end posting page. This page name and slug are set up through Dashboard > Fanfiction OPTIONS > Posting Page.

Dashboard > Fanfiction > Books
Add or edit Book titles and slugs. 

Dashboard > Fanfiction > Genres
Add or edit genres and slugs. 

Dashboard > Fanfiction > Fandoms.
If you choose to title this Fandoms (for a fanfiction site) or Category (for a General Writing Site) - this is where you can add or edit titles and slugs. NOTE: A tag cloud is generated for this. There is a Widget available for this, as well. 
You choose the title of Fandom or Category - or whatever you like - in Dashboard > Fanfiction OPTIONS > Fandom Label Single. 

Dashboard > Fanfiction > Ratings
Add or edit ratings and slugs. NOTE: we have included ratings images for G, PG-13, R, NC-17 with your plugin, and those images are mapped to these ratings. 

Dashboard > Fanfiction > Characters
Add or edit characters and slugs. 

Fiction Stats
There are no settings here, but you can see Analytics,  Summary, and Ranking List and sort by many parameters. 

CREATE PAGES

Archive/Library Page
Pages > Add New:  Create a new page and title it the same title you chose for Dashboard > Fanfiction OPTIONS  > Fanfiction Options. Insert shortcode [wp-fanfiction-writing-archive]. 

Search
Pages > Add New: To create a Search page, use the plugins WIDGETS ON PAGES, available for free in Plugins Directory. You can then use WP FFWA Story Search  in Widgets on Pages on your Widgets page. Then use the short code [widges_on_pages] on your search page.

Post
Pages > Add New: Create a new page and title it the same title you chose for Fanfiction Options > Posting Page. Insert shortcode [post_story]. 

You'll want to add these pages to your main navigation using Appearance > Menus.

Edit width of your library/archive display:
If your library or archive is displaying too wide, go to Fanfic Options > Fanfic Options and edit CSS:
edit CSS on the Fan Fiction Page Stylesheet option of Fanfic Option page.
If your theme is responsive, code should be like that,
@media only screen and (min-width: 1024px){
.single_fiction{
width:68%;}
}
@media only screen and (max-width: 1023px){
.single_fiction{
width:100%;}
}
If your theme is not responsive, code should be like that,
.single_fiction{
width:68%;}


== Frequently Asked Questions ==
Q. Is this plugin only for fanfiction sites? 
A: No. You can relabel fields and names in Dashboard > Fanfiction OPTIONS

Q. How is this Premium plugin different from the Basic plugin?
A: In addition to having the ability to relabel the fields to use this for any kind of writing site, there are additional features such as Auto-Tweet, 2 embedded advertising spots that can be easily set up, optimum SEO settings, and age verification option.

Q. How do I get the Author Profile and Favorites plugins? 
A. They are premium plugins available at http://writing-archive.com

Q. Do I get updates and new releases?
Yes, if you have an active API License, which you can get at http://writing-archive.com, you will find updates available on your Dashboard as they are released. 

Q. How do I get support or help? 
There will be a support forum at http://writing-archive.com for those with active API License keys. 

More FAQs comings soon. 

== Screenshots ==
1. Dashboard > Fanfiction Options, label your page where your stories will display.
2. Dashboard > Writings > Categories. Set up the categories that writers will be posting to.  Example: fiction, non-fiction, poetry. Example 2: Fandom 1, Fandom 2, Fandom 3, etc.

== Changelog ==
No changes. First version.

== Upgrade Notice ==
No changes. First version.