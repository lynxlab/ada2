function initEtherpad(etherData, appendSelector) {

    $j.when(getInstanceGroup(), getUserEthAuthor())
        .done((groupR, authorR) => {
            const group = groupR[0];
            const author = authorR[0];
            if (group.status == 'ERROR' || group.groupId == null) {
                handleError(group.msg);
            } else if (author.status == 'ERROR' || author.authorId == null) {
                handleError(author.msg);
            } else {
                $j.when(getPad(group.groupId), getSession(group.groupId, author.authorId))
                    .done((padR, sessionR)=>{
                        const pad = padR[0];
                        const session = sessionR[0];
                        if (pad.status == 'ERROR' || pad.padName == null) {
                            handleError(pad.msg);
                        } else if (session.status == 'ERROR' || session.sessionId == null) {
                            handleError(session.msg);
                        } else {
                            $j('#etherErrorBox').remove();
                            $j(appendSelector).append(buildIframe(session.sessionId, pad.padName));
                        }
                    }).fail(handleAjaxError);
            }
        }).fail(handleAjaxError);

    /**
     * helper functions
     */
    function handleAjaxError (error) {
        var msg = null;
        if ('responseText' in error) {
            msg = error.responseText;
            if ('status' in error) {
                msg += `(${error.status})`;
            }
        }
        handleError(msg);
    }

    function handleError(msg) {
        if (msg != null) {
            $j('#etherErrorMsg').html(msg);
        }
        $j('#etherErrorBox').show();
    }

    function getInstanceGroup() {
        return $j.ajax({
            method: 'GET',
            url: `${etherData.baseUrl}/ajax/getInstanceGroup.php`,
            data: {
                instanceId: etherData.instanceId,
            }
        });
    }

    function getUserEthAuthor() {
        return $j.ajax({
            method: 'GET',
            url: `${etherData.baseUrl}/ajax/getUserEthAuthor.php`,
            data: {
                userId: etherData.userId,
            }
        });
    }

    function getPad(groupId) {
        return $j.ajax({
            method: 'GET',
            url: `${etherData.baseUrl}/ajax/getPad.php`,
            data: {
                groupId: groupId,
                nodeId: etherData.nodeId,
            }
        });
    }

    function getSession(groupId, authorId) {
        return $j.ajax({
            method: 'GET',
            url: `${etherData.baseUrl}/ajax/getSession.php`,
            data: {
                groupId: groupId,
                authorId: authorId,
            }
        });
    }

    function buildIframe(sessionId, padName) {
        return `<iframe id="etherpad-integration" frameBorder="0" src="${etherData.etherpadUrl}/auth_session?sessionID=${sessionId}&padName=${padName}" allowfullscreen width="100%" height="500"></iframe>`;
    }

}