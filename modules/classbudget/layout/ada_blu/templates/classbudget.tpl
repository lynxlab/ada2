<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<body>
    <a name="top"></a>
    <div id="pagecontainer">
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!-- / testata -->
        <!-- menu -->
        <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
        <!-- / menu -->
        <!-- contenitore -->
        <div id="container">
            <!-- PERCORSO -->
            <div id="journey" class="ui tertiary inverted teal segment">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="title">title</template_field>
                </span>
            </div>
            <div id="user_wrap">
                <!--dati utente-->
                <div id="status_bar">
                    <div class="user_data_default status_bar">
                        <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                        <span>
                            <template_field class="template_field" name="message">message</template_field>
                        </span>
                    </div>
                </div>
                <!-- / dati utente -->
            </div>

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
                        <div id="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <div id="budgetresumeContainer">
                            <h2>
                                <i18n>Il budget per questa classe &egrave; di</i18n>
                                <template_field class="template_field" name="currency">currency</template_field>
                                <span id="instance-budget" data-instance-budget='<template_field class="template_field" name="budget">budget</template_field>'><template_field class="template_field" name="budgetStr">budgetStr</template_field></span> &nbsp;
                                <i18n>le spese ammontano a</i18n>: &nbsp;
                                <template_field class="template_field" name="currency">currency</template_field>&nbsp;
                                <span id="instance-cost" data-instance-totalcost='<template_field class="template_field" name="totalcost">totalcost</template_field>'><template_field class="template_field" name="totalcostStr">totalcostStr</template_field></span>
                                <br/>(
                                <i18n>differenza</i18n>:
                                <template_field class="template_field" name="currency">currency</template_field>
                                <span id="instance-balance" class='<template_field class="template_field" name="balanceclass">balanceclass</template_field>' data-instance-balance='<template_field class="template_field" name="balance">balance</template_field>'><template_field class="template_field" name="balanceStr">balanceStr</template_field></span>)
                            </h2>
                        </div>
                        <template_field class="template_field" name="data">data</template_field>
                    </div>
                </div>

                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->
        <div id="push"></div>
    </div>

    <!-- piede -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->

</body>

</html>
