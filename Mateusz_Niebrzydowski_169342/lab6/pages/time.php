<script src="js/timedate.js" type="text/javascript"></script>
<script src="js/kolorujtlo.js" type="text/javascript"></script>
<script>

window.addEventListener('DOMContentLoaded', function() {
    startclock();
});
</script>
<div id="time">
<section id="data" style="font-size: xxx-large; text-align: center"></section>
<section id="zegarek" style="font-size: xxx-large; text-align: center"></section>
<p style="font-size: xx-large; text-align: center">Zmień kolor tła:</p>
<br>
<form method="post" name="background" style="text-align: center">
    <input type="button" value="żółty" style="font-size: x-large" onclick="changeBackground('#FFF000')">
    <input type="button" value="czarny" style="font-size: x-large" onclick="changeBackground('#000000')">
    <input type="button" value="biały" style="font-size: x-large" onclick="changeBackground('#FFFFFF')">
    <input type="button" value="zielony" style="font-size: x-large" onclick="changeBackground('#00FF00')">
    <input type="button" value="niebieski" style="font-size: x-large" onclick="changeBackground('#0000FF')">
    <input type="button" value="pomarańczowy" style="font-size: x-large" onclick="changeBackground('#FF8000')">
    <input type="button" value="szary" style="font-size: x-large" onclick="changeBackground('#C0C0C0')">
    <input type="button" value="czerwony" style="font-size: x-large" onclick="changeBackground('#FF0000')">
</form>
</div>

