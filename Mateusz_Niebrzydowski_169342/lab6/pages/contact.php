<section class="center">
    <h2>Formularz kontaktowy</h2>
    <br>
    <form action="mailto:169342@student.uwm.edu.pl" method="post" enctype="text/plain">
        <table class="form">
            <tr>
                <td><label for="name">Imię:</label></td>
                <td><input type="text" id="name" name="name" required></td>
            </tr>
            <tr>
                <td><label for="email">Email:</label></td>
                <td><input type="email" id="email" name="email" size="30" required></td>
            </tr>
            <tr>
                <td><label for="message">Wiadomość:</label></td>
                <td><textarea id="message" name="message" rows="6" cols="50" required></textarea></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" value="Wyślij" id="submit"></td>
            </tr>
        </table>
    </form>
</section>

