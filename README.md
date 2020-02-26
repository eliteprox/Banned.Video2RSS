# Banned.Video2RSS
A PHP script which renders an RSS 2.0 feed from Banned.Video for all the latest videos. Allows you to optionally select by channel.

This is a simple PHP script which interfaces with the Banned.Video api and is useful for generating your own RSS feed from the latest videos or videos by channel. 
The result is a URL (this PHP file on your server) which can be added to an RSS Reader like Feedly to produce a full featured RSS Video Podcast like user experience on any mobile or desktop device.

### Usage:
* No parameters will return the latest videos as "latest.rss"
* **channel** parameter should be the channel id shown from the list below. Only one channel ID is allowed at a time. This will return a file called "channel.rss"
Example getRSS.php?channel=5b885d33e6646a0015a6fa2d

Channel IDs for your reference:

"_id": "5b885d33e6646a0015a6fa2d",
"title": "The Alex Jones Show",

"_id": "5b9301172abf762e22bc22fd",
"title": "War Room With Owen Shroyer",

"_id": "5b92d71e03deea35a4c6cdef",
"title": "The David Knight Show",

"_id": "5d7a86b1f30956001545dd71",
"title": "Paul Joseph Watson",

"_id": "5d7faf08b468d500160c8e3f",
"title": "Kaitlin Bennett",

"_id": "5d7faa8432b5da0013fa65bd",
"title": "Millie Weaver",

"_id": "5d8d03dbd018a5001776876a",
"title": "Bowne Report",

"_id": "5da504e060be810013cf7252",
"title": "Greg Reese",

"_id": "5b9429906a1af769bc31efeb",
"title": "Special Reports",

"_id": "5cf7df690a17850012626701",
"title": "Mike Adams",

"_id": "5da8c506da090400138c8a6a",
"title": "Darrin McBreen",

"_id": "5dbb4729ae9e840012c61293",
"title": "Harrison Smith",

"_id": "5df02803bc00a5002bb18efa",
"title": "Rex Jones",

"_id": "5e18fe64840aee002b406f88",
"title": "Action 7",

"_id": "5e3de10780b773002546fd11",
"title": "Red Pilled TV",

"_id": "5dae2e7f612f0a0012d147bf",
"title": "Trends with Gerald Celente",

"_id": "5d9653676f2d2a00179b8a58",
"title": "Jake Lloyd",

"_id": "5d7fa9014ffcfc00130304fa",
"title": "MemeWorld",

"_id": "5d72c972f230520013554291",
"title": "Fire Power News",
