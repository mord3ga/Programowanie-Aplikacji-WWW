<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<section id="animacjaTestowa1" class="text-block">Kliknij, a się powiększę</section>
<script>
    $("#animacjaTestowa1").on("click", function() {
        $(this).animate({
            width: "500px",
            opacity: 0.4,
            fontSize: "3em",
            borderWidth: "10px"
        }, 1500);
    });
</script>
<section id="animacjaTestowa2" class="text-block">Najedź kursorem, a się powiększę</section>
<script>
    $("#animacjaTestowa2").on({
        "mouseover" : function() {
            $(this).animate({
                width: 300
            }, 800);
        },
        "mouseout" : function() {
            $(this).animate({
                width: 200
            }, 800);
        }
    });
</script>
<section id="animacjaTestowa3" class="text-block">Klikaj, abym rósł</section>
<script>
    $("#animacjaTestowa3").on("click", function() {
        if (!$(this).is(":animated")) {
            $(this).animate({
                width: "+=" + 50,
                height: "+=" + 10
            });
        }
    });
</script>

