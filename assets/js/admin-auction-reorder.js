const init_reorder_auction = () => {
    const reorderButtons = document.getElementsByClassName("auction-reorder")
    for(let i=0; i<reorderButtons.length; i++) {
        reorderButtons[i].addEventListener("click", reorderAuction)
    }
    const closeButton = document.getElementsByClassName("auction-reorder-close")[0]
    closeButton.addEventListener("click", closeReorder)

    const submitReorder = document.getElementsByClassName("reorder-submit")[0]
    submitReorder.addEventListener("click", submitAuctionReorder)
}
const showRorderModal = () => {
    const modal = document.getElementById("auction-reorder-modal");
    modal.style.display = "block";
}
const reorderAuction = event => {
    event.stopPropagation()
    self = event.currentTarget
    termId = self.dataset.termId
    modalBody = document.getElementsByClassName("auction-reorder-body")[0]

    payload = {
        action: "get_auction_items",        
        term_id: termId
    }

    const headerTitle = document.getElementById("auction-group-header")
    headerTitle.innerText = "Auction Group : " + self.dataset.termName

    var html = ""
    showModalLoading()
    jQuery.ajax({
        type : "post",
        dataType : "json",
        url : admin_ajax.ajaxurl,
        data : payload,
        success: function(response) {
            attachments = JSON.parse(response.attachments)
            for(i=0;i<attachments.length;i++) {
                itemID = attachments[i].id
                imgSrc = attachments[i].src
                imgCaption = attachments[i].caption
                html += '<div class="item auction-item" data-real-id="' + itemID + '" id="reorder-' + itemID + '" draggable="true"><img class="auction-item-image" src="' + imgSrc + '"><span>' + imgCaption + '</span></div>'
            }            
            setHTMLContent(modalBody, html)
            init_auction_reorder_dragndrop()
        }
    })
    showRorderModal()
}

const closeReorder = event => {
    const modal = document.getElementById("auction-reorder-modal");
    modal.style.display = "none";
}

const showModalLoading = () => {
    modalBody = document.getElementsByClassName("auction-reorder-body")[0]
    modalBody.innerHTML = '<img src="http://kkf.loc:8888/wp-content/uploads/2020/12/loading-icon.gif" style="margin:0 auto;height:200px">'
}

const showSuccessfulMessage = () => {
    modalBody = document.getElementsByClassName("auction-reorder-body")[0]
    modalBody.innerHTML = '<div class="reorder-success"> Successfully Saved </div>'
}

const disableAuctionImageDraggable = () => {
    auctionImages = document.getElementsByClassName('auction-item-image')
    for(i=0;i<auctionImages.length;i++) {
        auctionImages[i].setAttribute('draggable', false);
    }
}

const get_auction_items = () => document.getElementsByClassName("auction-item")

const init_auction_reorder_dragndrop = () => {
    disableAuctionImageDraggable()

    const auctionItems = get_auction_items()
    for(let i=0; i<auctionItems.length; i++) {
        auctionItems[i].addEventListener("dragstart", dragItemHandler)
        auctionItems[i].addEventListener("drop", dropItemHandler)
        auctionItems[i].addEventListener("dragenter", dragEnterHandler)
        auctionItems[i].addEventListener("dragleave", dragLeaveHandler)
        auctionItems[i].addEventListener("dragover", dragOverHandler)
    }
}

const dragItemHandler = event => {
    self = event.currentTarget
    event.dataTransfer.setData("text", self.id);
}

const dropItemHandler = event => {
    event.preventDefault();
    self = event.currentTarget    
    itemA = document.getElementById(event.dataTransfer.getData("text"))
    itemB = self.id
    self.parentNode.insertBefore(itemA, self)
    self.classList.remove('hover')
    
}
const submitAuctionReorder = event => {
    const auctionItems = get_auction_items()
    var auctionIds = []
    for(i=0;i<auctionItems.length;i++) {
        auctionIds.push(auctionItems[i].dataset.realId)        
    }
    payload = {
        action: "auction_reorder",
        items: auctionIds
    }
    modalBody = document.getElementsByClassName("auction-reorder-body")[0]
    modalBodyHtml = modalBody.innerHTML

    showModalLoading()
    jQuery.ajax({
        type : "post",
        dataType : "json",
        url : admin_ajax.ajaxurl,
        data : payload,
        success: function(response) {
            showSuccessfulMessage()
            setTimeout(() => {
                setHTMLContent(modalBody, modalBodyHtml)
                init_auction_reorder_dragndrop()
            }, 1000)
        }
    })
    
}