HideMod v1.0
--------------------
This plugin adds the possibility to hide stuff from members.
If the want to see it, they have to like (\[hide\]) the post or reply (\[hide-reply\]).

Features:
---------
  - hide and hide-reply
  - fully changeable html code for locked/unlocked before and after...
  - Disable Hide in specific boards

Installation:
-------------

Drop "hide" folder in your /wedgefolder/plugins and activate it in the adminpanel.
If you want you can change the default messages. Have a look at the next point.

Example German Config:
----------------------

Unter Plugins --> HideMod:

[hide] Locked: ``<center><div id="profile_error" class="windowbg">Du musst diesen Beitrag liken um den Inhalt sehen zu können.</div></center>``

[hide] unlocked before: ``<center><div id="profile_success" class="windowbg">Unhidden Content:</div></center>``


[hide-reply] Locked: ``<center><div id="profile_error" class="windowbg">Du musst antworten um den Inhalt sehen zu können.</div></center>``

[hide-reply] unlocked before: ``<center><div id="profile_success" class="windowbg">Unhidden Content:</div></center>``

Rest bleibt leer ;)

To-Do:
------
- add some more settings & language
- as soon as wedge adds a similar hook for quote_fast_done im going to rewrite the whole code
- Better Html
- improve bbc parse code
