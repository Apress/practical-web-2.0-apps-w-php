            </div>
        </div>

        <div id="left-container" class="column">
            <div class="box" id="search">
                <form method="get" action="{geturl controller='search'}">
                    <div>
                        <input type="text" name="q" value="{$q|escape}"
                               id="search-query" />
                        <input type="submit" value="Search" />
                    </div>
                </form>
            </div>

            {if isset($leftcolumn) && $leftcolumn|strlen > 0}
                {include file=$leftcolumn}
            {/if}
        </div>

        <div id="right-container" class="column">
            {if $messages|@count > 0}
                <div id="messages" class="box">
                    {if $messages|@count == 1}
                        <strong>Status Message:</strong>
                        {$messages.0|escape}
                    {else}
                        <strong>Status Messages:</strong>
                        <ul>
                            {foreach from=$messages item=row}
                                <li>{$row|escape}</li>
                            {/foreach}
                        </ul>
                    {/if}
                </div>
            {else}
                <div id="messages" class="box" style="display:none"></div>
            {/if}

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

            {if isset($rightcolumn) && $rightcolumn|strlen > 0}
                {include file=$rightcolumn}
            {/if}
        </div>

        <div id="footer">
            Practical PHP Web 2.0 Applications, by Quentin Zervaas.
        </div>
    </body>
</html>
