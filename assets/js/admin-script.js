jQuery(document).ready(function() {
    jQuery('.datepicker').datepicker()
    jQuery('.filter-submit').click(submitFilter)
});
const init_refresh_filter = () => {
    const refreshFilter = document.getElementById("refresh-filter")
    refreshFilter.addEventListener("click", refreshFilterListener)
}

const init_pagination = () => {
    const pagelinks = document.getElementsByClassName("koi-pricing-pagelinks")
    for(let i=0; i<pagelinks.length; i++) {
        pagelinks[i].addEventListener("click", pageLinkClickListener)
    }
}

const init_dragndrop = () => {
    const dragIcons = document.getElementsByClassName("draggable-icon")
    for(let i=0; i<dragIcons.length; i++) {
        dragIcons[i].addEventListener("dragstart", dragRowHandler)
    }

    const auction_group_items = document.getElementsByClassName("taxonomy-term-list-item")
    for(let i=0; i<auction_group_items.length; i++) {
        auction_group_items[i].addEventListener("dragenter", inventoryDragEnterHandler)
        auction_group_items[i].addEventListener("dragleave", inventoryDragLeaveHandler)
        auction_group_items[i].addEventListener("dragover", inventoryDragOverHandler)
        auction_group_items[i].addEventListener("drop", inventoryDropHandler)
        auction_group_items[i].addEventListener("click", termClickHandler)
    }
}
const getKoiTableBody = () => document.getElementById('koi-pricing-table-body')
const getPaginationContainer = () => document.getElementById('koi-pricing-pagination')
const getActiveFilterContainer = () => document.getElementById('current-filter-info')

const setHTMLContent = (element, htmlContent) => {
    htmlContent = (htmlContent === '') ? '<span style="padding: 10px">No Results</span>' : htmlContent
    element.innerHTML = htmlContent
    init_event_listeners()
}
const refreshFilterListener = event => {
    koiTableBody = getKoiTableBody()
    paginationContainer = getPaginationContainer()
    activeFilterContainer = getActiveFilterContainer()

    showLoading()

    payload = {
        action: "refresh_filter"
    }

    jQuery.ajax({
        type : "post",
        dataType : "json",
        url : admin_ajax.ajaxurl,
        data : payload,
        success: function(response) {
            setHTMLContent(koiTableBody, response.data)
            setHTMLContent(paginationContainer, response.pagination)
            setHTMLContent(activeFilterContainer, response.active_filter)
        }
    })
}

const pageLinkClickListener = event => {
    koiTableBody = getKoiTableBody()
    self = event.currentTarget
    offset = self.dataset.offset

    payload = {
        action: "change_current_page",
        offset: offset
    }
    showLoading()

    jQuery.ajax({
        type : "post",
        dataType : "json",
        url : admin_ajax.ajaxurl,
        data : payload,
        success: function(response) {            
            setHTMLContent(koiTableBody, response.data)
        }
    })
}

const termClickHandler = event => {
    koiTableBody = getKoiTableBody()
    paginationContainer = getPaginationContainer()
    activeFilterContainer = getActiveFilterContainer()

    self = event.currentTarget
    taxonomy = self.dataset.taxonomy
    termId = self.dataset.termId

    payload = {
        action: "filter_by_term",
        taxonomy: taxonomy,
        term_id: termId
    }
    showLoading()    

    jQuery.ajax({
        type : "post",
        dataType : "json",
        url : admin_ajax.ajaxurl,
        data : payload,
        success: function(response) {            
            setHTMLContent(koiTableBody, response.data)
            setHTMLContent(paginationContainer, response.pagination)
            setHTMLContent(activeFilterContainer, response.active_filter)
        }
    })
}

const dragRowHandler = event => {
    event.dataTransfer.setData("text", event.target.id)
    const parentRow = event.target.parentElement.parentElement;
    event.dataTransfer.setDragImage(parentRow, -50, -50);
}

const inventoryDragEnterHandler = event => {
    var self = event.currentTarget
    self.classList.add('hover')
}
const inventoryDragLeaveHandler = event => {
    var self = event.currentTarget
    self.classList.remove('hover')
}

const inventoryDragOverHandler = event => {
    event.preventDefault()
}

const inventoryDropHandler = event => {
    var self = event.currentTarget
    postID = event.dataTransfer.getData("text");
    taxonomy = self.dataset.taxonomy
    self.classList.remove('hover')
    
    payload = {
        action: "assign_taxonomy_term",
        taxonomy: taxonomy,
        term_id: self.id,
        post_id: postID
    }

    jQuery.ajax({
        type : "post",
        dataType : "json",
        url : admin_ajax.ajaxurl,
        data : payload,
        success: function(response) {
            triggerNotification()
        }
    })    
}


const submitFilter = () => {
    koiTableBody = getKoiTableBody()
    let payload = {action: 'filter_koi_handler'}
    jQuery('.koi-pricing-table-filter input').each(function() {       
        payload[jQuery(this).attr('name')] = jQuery(this).val()
    })
    showLoading()
    
    jQuery.ajax({
        type : "post",
        dataType : "json",
        url : admin_ajax.ajaxurl,
        data : payload,
        success: function(response) {
            setHTMLContent(koiTableBody, response.data)
        }
    })
}

const init_accordion = () => {
    var acc = document.getElementsByClassName("accordion")
    var i

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("dragenter", expandAccordion)
        acc[i].addEventListener("click", expandAccordion);
    }
}

const expandAccordion = event => {
    var self = event.currentTarget
    if(self.classList.contains("active") && event.type === "dragenter") return

    const fasIcon = self.children[0]
    if(fasIcon.classList.contains("fa-folder")) {
        fasIcon.classList.remove("fa-folder")
        fasIcon.classList.add("fa-folder-open")
    }
    else {
        fasIcon.classList.remove("fa-folder-open")
        fasIcon.classList.add("fa-folder")
    }

    self.classList.toggle("active")
    var panel = self.nextElementSibling
    if (panel.style.maxHeight) {
        panel.style.maxHeight = null
    } else {
        panel.style.maxHeight = panel.scrollHeight + "px"
    }
}

const triggerNotification = () => {
    jQuery('.koi-pricing-notification').slideDown(700)
    setTimeout(() => {
        jQuery('.koi-pricing-notification').slideUp(700)
    }, 3000)
    
}
const showLoading = () => {
    koiTableBody = getKoiTableBody()
    koiTableBody.innerHTML = '<tr><td colspan="6" style="text-align:center"><img src="http://kkf.loc:8888/wp-content/uploads/2020/12/loading-icon.gif" style="height:200px"></td></tr>'
}