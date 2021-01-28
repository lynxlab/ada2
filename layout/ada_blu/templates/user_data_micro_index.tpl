<div id="status_bar">
    <div class="user_data_default status_bar">
        <div class="avatar-container">
            <span class="avatar_user ui small image">
                <template_field class="template_field" name="user_avatar">user_avatar</template_field>
            </span>
        </div>
        <div class="user-data-container">
            <span>
                <i18n>utente: </i18n>
                <span class="uname">
                    <template_field class="template_field" name="user_uname">user_uname</template_field>
                </span>
            </span>
            <span>
                <i18n>tipo: </i18n>
                <template_field class="template_field" name="user_type">user_type</template_field>
            </span>
            <span>
                <i18n>status: </i18n>
                <template_field class="template_field" name="status">status</template_field>
            </span>
        </div>
        <div class="impersonate-link-container">
            <a class="ui tiny button impersonatelink" href="<template_field class="template_field" name="user_modprofilelink">user_modprofilelink</template_field>">
                <i18n>Vai alla tua Home</i18n>
            </a>
        </div>
    </div>
</div>
