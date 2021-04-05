AsdIblockElementList = (function() {
	/** @param {{
	* 		resultDiv: string
		}} params
	 */
	var AsdIblockElementListDescr = function(params) {
		this.resultDiv = null;

		this.resultDivId = '';

		if (BX.type.isPlainObject(params)) {
			if (BX.type.isNotEmptyString(params.resultDiv))
				this.resultDivId = params.resultDiv;
		}

		BX.ready(BX.proxy(this.init, this));
	};

	AsdIblockElementListDescr.prototype.init = function() {
		if (this.resultDivId.length > 0) {
			this.resultDiv = BX(this.resultDivId);
			if (BX.type.isElementNode(this.resultDiv)) {
				BX.bindDelegate(this.resultDiv, 'keydown', { tagName: 'input', attr: { 'type': 'text' } }, BX.proxy(function(event){ this.handlerKeyDown(event); }, this));
				BX.bindDelegate(this.resultDiv, 'keydown', { tagName: 'select' }, BX.proxy(function(event){ this.handlerKeyDown(event); }, this));
				BX.bindDelegate(this.resultDiv, 'keydown', { tagName: 'textarea' }, BX.proxy(function(event){ this.handlerKeyDown(event); }, this));
			}
		}

	};

	AsdIblockElementListDescr.prototype.destroy = function() {
		if (BX.type.isElementNode(this.resultDiv)) {
			BX.unbindAll(this.resultDiv);
		}
	};

	AsdIblockElementListDescr.prototype.handlerKeyDown = function(event) {
		var target = BX.proxy_context,
			cell,
			row,
			list,
			xCoord,
			yCoord,
			filter,
			found = false,
			i,
			newCell,
			newTarget;

		if (!event.ctrlKey || !BX.util.in_array(event.keyCode, [37, 38, 39, 40])) {
			return;
		}
		if (!this.isAllowedElement(target)) {
			return;
		}
		cell = BX.findParent(target, this.getCellFilter(), this.resultDiv);
		if (!BX.type.isElementNode(cell)) {
			return;
		}
		row = cell.parentNode;
		xCoord = cell.cellIndex;

		switch (event.keyCode) {
			case 37:
			case 39:
				filter = this.getHorizontalFilter();
				if (event.keyCode == 37) {
					if (xCoord > 0) {
						for (i = xCoord - 1; i >= 0; i--) {
							newTarget = BX.findChild(row.cells[i], filter, true, false);
							if (BX.type.isElementNode(newTarget)) {
								newCell = row.cells[i];
								found = true;
								break;
							}
						}
					}
				} else {
					if (xCoord < (row.cells.length - 1)) {
						for (i = xCoord + 1; i < row.cells.length; i++) {
							newTarget = BX.findChild(row.cells[i], filter, true, false);
							if (BX.type.isElementNode(newTarget)) {
								newCell = row.cells[i];
								found = true;
								break;
							}
						}
					}
				}
				break;
			case 38:
			case 40:
				list = row.parentNode;
				yCoord = row.sectionRowIndex;
				filter = this.getVerticalFilter(target);
				if (event.keyCode == 38) {
					if (yCoord > 0) {
						for (i = yCoord - 1; i >= 0; i--) {
							newTarget = BX.findChild(list.rows[i].cells[xCoord], filter, true, false);
							if (BX.type.isElementNode(newTarget)) {
								newCell = list.rows[i].cells[xCoord];
								found = true;
								break;
							}
						}
					}
				} else {
					if (yCoord < (list.rows.length - 1)) {
						for (i = yCoord + 1; i < list.rows.length; i++) {
							newTarget = BX.findChild(list.rows[i].cells[xCoord], filter, true, false);
							if (BX.type.isElementNode(newTarget)) {
								newCell = list.rows[i].cells[xCoord];
								found = true;
								break;
							}
						}
					}
				}
				filter = null;
				list = null;
				break;
		}
		if (found) {
			BX.focus(newTarget);
			return BX.PreventDefault(event);
		}

		newTarget = null;
		newCell = null;

		row = null;
		cell = null;
	};

	AsdIblockElementListDescr.prototype.isAllowedElement = function(node) {
		if (node.tagName === 'INPUT' && node.getAttribute('type').toLowerCase() === 'text') {
			return true;
		} else if (node.tagName === 'SELECT' || node.tagName === 'TEXTAREA') {
			return true;
		}
		return false;
	};

	AsdIblockElementListDescr.prototype.getVerticalFilter = function(node) {
		if (node.tagName === 'INPUT' && node.getAttribute('type').toLowerCase() === 'text') {
			return { tagName: node.tagName, attr: { 'type': 'text' } };
		} else if (node.tagName === 'SELECT' || node.tagName === 'TEXTAREA') {
			return { tagName: node.tagName };
		}
		return null;
	};

	AsdIblockElementListDescr.prototype.getHorizontalFilter = function() {
		return {
			'callback': this.isAllowedElement
		};
	};

	AsdIblockElementListDescr.prototype.getCellFilter = function() {
		return { tagName: 'TD', className: 'adm-list-table-cell' }
	};

	return AsdIblockElementListDescr;
})();