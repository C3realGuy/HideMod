HideMod v1.0 (alpha)
--------------------
Hey here is my first plugin for wedge :)
Its a simple HideMod. It supports [hide] (you need to like) and [hide-reply].

Features:
---------
  - hide and hide-reply
  - fully changeable html code for locked/unlocked before and after...

Installation:
-------------

Drop "hide" folder in your /wedgefolder/plugins and activate it in the adminpanel.
At the moment you need to add an own hook into wedge because there's no one to handle
quoting stuff (and if we dont handle it, its possible to look threw the hide when you
quote^^)
Therefore you need to make two small changes in your core files:


In /core/app/ManagePlugins.php
Search ``// Content creation`` and add afterwards ``'quote_fast_done',``

In /core/app/QuoteFast.php
Search ``return_xml('<we><quote>', cleanXml($xml), '</quote></we>');``
and add before ``call_hook('quote_fast_done', array(&$xml, &$_REQUEST['quote'], &$row));``

Example German Config:
----------------------

Unter Plugins --> HideMod:

[hide] Locked: ``<center><div id="profile_error" class="windowbg">Du musst diesen Beitrag liken um den Inhalt sehen zu können.</div></center>``

[hide] unlocked before: ``<center><div id="profile_success" class="windowbg">Unhidden Content:</div></center>``


[hide-reply] Locked: ``<center><div id="profile_error" class="windowbg">Du musst antworten um den Inhalt sehen zu können.</div></center>``

[hide-reply] unlocked before: ``<center><div id="profile_success" class="windowbg">Unhidden Content:</div></center>``

Rest bleibt leer ;)


To-Do:
- Plugin icon
- Better Html
- block unlike?!
- on like automatic reload of page
- as soon as wedge adds a similar hook for quote_fast_done im going to rewrite the hole code
