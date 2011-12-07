function embedPlayer(url, type)
{
   var objContentType = (navigator.userAgent.toLowerCase().indexOf("windows") != -1) ? "application/x-mplayer2" : "audio/mpeg";
   var height = (type == 'audio') ? 69 : 270;

   document.writeln("<div>");
   document.writeln("<object width='280' height='" + height + "'>");
   document.writeln("<param name='type' value='" + objContentType + "'>");
   document.writeln("<param name='src' value='" + url + "'>");
   document.writeln("<param name='autostart' value='0'>");
   document.writeln("<param name='showcontrols' value='1'>");
   document.writeln("<param name='showstatusbar' value='1'>");
   document.writeln("<embed src ='" + url + "' type='" + objContentType + "' autoplay='false' autostart='0' width='280' height='" + height + "' controller='1' showstatusbar='1' bgcolor='#ffffff'></embed>");
   document.writeln("</object>");
   document.writeln("</div>");
   document.close();
}

function embedFile(id, mediatype)
{
    var url = '?p=digitallibrary/getfile&amp;id=' + id;

    if(arguments[2])
    {
        url += '&amp;preview=' + arguments[2];
    }

    if(mediatype == 'Image')
    {
        document.writeln("<img class='digcontentfile' src='" + url + "' alt='Image associated with ID number" + id + "'>");
    }
    else if(mediatype == 'Audio')
    {
        embedPlayer(url, 'audio');
    }
    else if(mediatype == 'Video')
    {
        embedPlayer(url, 'video');
    }
}