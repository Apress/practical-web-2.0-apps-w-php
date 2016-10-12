            </div>
        </div>

        <div id="left-container" class="column">
            <div class="box">
                Left column placeholder
            </div>
        </div>

        <div id="right-container" class="column">
            <div class="box">
                {if $authenticated}
                    Logged in as
                    {$identity->first_name|escape} {$identity->last_name|escape}
                    (<a href="{geturl controller='account' action='logout'}">logout</a>).
                    <a href="{geturl controller='account' action='details'}">Update details</a>.
                {else}
                    You are not logged in.
                    <a href="{geturl controller='account' action='login'}">Log in</a> or
                    <a href="{geturl controller='account' action='register'}">register</a> now.
                {/if}
            </div>
        </div>

        <div id="footer">
            Practical PHP Web 2.0 Applications, by Quentin Zervaas.
        </div>
    </body>
</html>
