var closedSections = [];

/**
 * Register an initially closed section.
 *
 * NOTE: this method does *not* close the section!
 */
function addClosedSection(section) {
    closedSections.push(section);
}

/**
 * Toggle section visibility.
 */
function handleSectionToggle(element, expand, url) {
    element = $(element);

    // automatically determine if we need to expand or collapse
    if (expand == null) {
        expand = closedSections.indexOf(element.id) >= 0;
    }

    $$('tr', 'div.atkSection', 'div.section-item').select(function (tr) {
        return $(tr).hasClassName(element.id);
    }).each(function (tr) {
        if (expand) {
            Element.show(tr);
        } else {
            Element.hide(tr);
        }
    });

    icon = $("img_"+element.id);
    if (expand) {
        var param = 'opened';
        icon.removeClassName('fa-plus-square-o');
        icon.addClassName('fa-minus-square-o');
        closedSections = closedSections.without(element.id);
    } else {
        var param = 'closed';
        icon.removeClassName('fa-minus-square-o');
        icon.addClassName('fa-plus-square-o');
        closedSections.push(element.id);
    }

    new Ajax.Request(url, {
        method: 'get',
        parameters: 'atksectionstate=' + param
    });
}

function isAttributeTr(tr) {
    return tr.id.substring(0, 3) == 'ar_';
}

/**
 * Sets the current tab
 */
function showTab(tab) {
    // If we are called without a name, we check if the parent has a stored tab for our page
    // If so, then we go there, else we go to the first tab (most of the time the 'default' tab)
    if (!tab) {
        tab = getCurrentTab();
        if (tab) {
            // However if for some reason this tab does not exist, we switch to the default tab
            if (!document.getElementById('tab_' + tab))
                tab = tabs[0];
        }
        else {
            tab = tabs[0];
        }
    }

    // Then we store what tab we are going to visit in the parent
    setCurrentTab(tab);

    var tabSectionName = 'section_' + tab;

    $$('tr', 'div.atkSection', 'div.section-item').select(isAttributeTr).each(function (tr) {
        var visible =
            $(tr).classNames().find(function (sectionName) {
                return sectionName.substring(0, tabSectionName.length) == tabSectionName &&
                    closedSections.indexOf(sectionName) < 0;
            }) != null;

        if (visible) {
            Element.show(tr);
            ATK.enableSelect2(jQuery(tr));
        }
        else {
            Element.hide(tr);
        }
    });

    // Then when set the colors or the tabs, the active tab gets a different color
    for (j = 0; j < tabs.length; j++) {
        if (document.getElementById('tab_' + tabs[j])) {
            if (tabs[j] == tab) {
                document.getElementById('tab_' + tabs[j]).className = 'activetab active';
            }
            else {
                document.getElementById('tab_' + tabs[j]).className = 'passivetab';
            }
        }
    }

    // make tabs visible (to avoid reload quirks, they load invisible from the html
    wrapper = document.getElementById('tabtable');
    if (wrapper) {
        wrapper.style.display = '';
    }
}


function getCurrentTab() {
    var getUriParams = function (name) {
        return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null;
    };
    if (getUriParams('stateful') == '1') {
        return 'getTab(getCurrentNodetype(), getCurrentSelector())';
    }
    return '';

}

function getTab(nodetype, selector) {
    _initTabArray(nodetype, selector);
    return parent.document.tab[nodetype][selector];
}

function setCurrentTab(value) {
    setTab(getCurrentNodetype(), getCurrentSelector(), value);

    for (var i = 0; i < document.forms.length; i++) {
        var form = document.forms[i];
        if (form.atktab != null) {
            form.atktab.value = value;
            form.atktab.defaultValue = value;
        }
        else {
            var input = document.createElement('input');
            input.setAttribute('type', 'hidden');
            input.setAttribute('name', 'atktab');
            input.setAttribute('value', value);
            input.defaultValue = value;
            form.appendChild(input);
        }
    }
}

function setTab(nodetype, selector, value) {
    _initTabArray(nodetype, selector);
    parent.document.tab[nodetype][selector] = value;
}

/**
 * Makes sure we don't get any nasty JS errors by making sure
 * the arrays we use are always set before using them.
 */
function _initTabArray(nodetype, selector) {
    if (!parent.document.tab)
        parent.document.tab = Array();
    if (!parent.document.tab[nodetype])
        parent.document.tab[nodetype] = Array();
}
