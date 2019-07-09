class WatchlistSubmitListener
{
    constructor(element)
    {
        this.element = element;
    }

    onSubmitEvent(event)
    {
        event.preventDefault();
        this.doProcessSubmitEvent(event);
    }

    /**
     * This method must be implemented by subclasses
     * @param event
     */
    doProcessSubmitEvent(event)
    {
        throw "This method must be implemented by the child class!";
    }

    serialize (form)
    {
        let field,
            formData = [],
            hasError = false;

        if (typeof form === 'object' && "FORM" === form.nodeName) {
            for (let i = 0; i < form.elements.length; i++) {
                field = form.elements[i];

                let name = field.name,
                    value = field.value;

                if ('checkbox' === field.type || 'radio' === field.type) {
                    value = field.checked;
                }

                if(field.required && !value) {
                    let error = document.createElement('span');
                    error.setAttribute('class', 'pt-1 d-block text-danger');
                    error.textContent = 'Bitte füllen Sie dieses Feld aus!';

                    let node = ('checkbox' === field.type || 'radio' === field.type) ?  field.parentNode.parentNode : field.parentNode;
                    node.appendChild(error);
                    hasError = true;
                }

                formData[name] = value;
            }
        }

        if(hasError) {
            return null;
        }

        return formData;
    }
}

class WatchlistAddItemListener extends WatchlistSubmitListener
{
    doProcessSubmitEvent(event) {
        this.addItem(event.target);
    }

    addItem (form)
    {
        let formData = this.serialize(form);
        if (null === formData)
        {
            return;
        }

        let uuid = false;
        let title = false;
        let itemOptions = null;

        if (!('uuid' in formData) || !('title' in formData))
        {
            let itemOptions = document.querySelector('.item-options option:selected');
        }

        uuid = ('uuid' in formData) ? formData.uuid : ((null !== itemOptions) ? itemOptions.value : null);
        title = ('title' in formData) ? formData.title : ((null !== itemOptions) ? itemOptions.textContent : null);

        let url = form.action,
            downloadable = formData.downloadable,
            moduleId = formData.moduleId,
            type = formData.type,
            options = formData.options ? formData.options : null,
            requestToken = formData.REQUEST_TOKEN,
            data = {
                'moduleId': moduleId,
                'type': type,
                'itemData': {
                    'options': options,
                    'uuid': uuid ? uuid : null,
                    'title': title ? title : null,
                    'downloadable': downloadable
                },
                'REQUEST_TOKEN': requestToken
            };

        Watchlist.doAjaxCallWithUpdate(url, data);
    }
}

export { WatchlistAddItemListener };