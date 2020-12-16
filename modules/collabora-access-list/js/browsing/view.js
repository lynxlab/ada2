function nodeAttachmentsDone(response, jElement) {
    if (jElement.parents('.ui.accordion').length>0) {
        const container = jElement.parents('.ui.accordion');
        // attach jElement content to its parent container, removing the ADAWidget class div
        $j(jElement.html()).appendTo(container);
        $j(jElement, container).remove();
        // jquery hack to get the parent accordion html without script tags
        const newAccordion = $j('script', $j("<div/>").append(container.first().html())).remove().end().html();
        container.html(newAccordion).accordion();
    }
}
