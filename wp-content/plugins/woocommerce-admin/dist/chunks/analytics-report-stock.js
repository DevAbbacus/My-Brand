(window["__wcAdmin_webpackJsonp"] = window["__wcAdmin_webpackJsonp"] || []).push([[17],{

/***/ 481:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "default", function() { return /* binding */ stock_StockReport; });

// EXTERNAL MODULE: ./node_modules/@babel/runtime/helpers/classCallCheck.js
var classCallCheck = __webpack_require__(11);
var classCallCheck_default = /*#__PURE__*/__webpack_require__.n(classCallCheck);

// EXTERNAL MODULE: ./node_modules/@babel/runtime/helpers/createClass.js
var createClass = __webpack_require__(12);
var createClass_default = /*#__PURE__*/__webpack_require__.n(createClass);

// EXTERNAL MODULE: ./node_modules/@babel/runtime/helpers/inherits.js
var inherits = __webpack_require__(13);
var inherits_default = /*#__PURE__*/__webpack_require__.n(inherits);

// EXTERNAL MODULE: ./node_modules/@babel/runtime/helpers/possibleConstructorReturn.js
var possibleConstructorReturn = __webpack_require__(14);
var possibleConstructorReturn_default = /*#__PURE__*/__webpack_require__.n(possibleConstructorReturn);

// EXTERNAL MODULE: ./node_modules/@babel/runtime/helpers/getPrototypeOf.js
var getPrototypeOf = __webpack_require__(6);
var getPrototypeOf_default = /*#__PURE__*/__webpack_require__.n(getPrototypeOf);

// EXTERNAL MODULE: external {"this":["wp","element"]}
var external_this_wp_element_ = __webpack_require__(0);

// EXTERNAL MODULE: ./node_modules/prop-types/index.js
var prop_types = __webpack_require__(1);
var prop_types_default = /*#__PURE__*/__webpack_require__.n(prop_types);

// EXTERNAL MODULE: external {"this":["wp","i18n"]}
var external_this_wp_i18n_ = __webpack_require__(2);

// EXTERNAL MODULE: external {"this":["wp","hooks"]}
var external_this_wp_hooks_ = __webpack_require__(42);

// CONCATENATED MODULE: ./client/analytics/report/stock/config.js
/**
 * External dependencies
 */


var STOCK_REPORT_FILTERS_FILTER = 'woocommerce_admin_stock_report_filters';
var STOCK_REPORT_ADVANCED_FILTERS_FILTER = 'woocommerce_admin_stock_report_advanced_filters';
var showDatePicker = false;
var config_filters = Object(external_this_wp_hooks_["applyFilters"])(STOCK_REPORT_FILTERS_FILTER, [{
  label: Object(external_this_wp_i18n_["__"])('Show', 'woocommerce-admin'),
  staticParams: ['paged', 'per_page'],
  param: 'type',
  showFilters: function showFilters() {
    return true;
  },
  filters: [{
    label: Object(external_this_wp_i18n_["__"])('All Products', 'woocommerce-admin'),
    value: 'all'
  }, {
    label: Object(external_this_wp_i18n_["__"])('Out of Stock', 'woocommerce-admin'),
    value: 'outofstock'
  }, {
    label: Object(external_this_wp_i18n_["__"])('Low Stock', 'woocommerce-admin'),
    value: 'lowstock'
  }, {
    label: Object(external_this_wp_i18n_["__"])('In Stock', 'woocommerce-admin'),
    value: 'instock'
  }, {
    label: Object(external_this_wp_i18n_["__"])('On Backorder', 'woocommerce-admin'),
    value: 'onbackorder'
  }]
}]);
var config_advancedFilters = Object(external_this_wp_hooks_["applyFilters"])(STOCK_REPORT_ADVANCED_FILTERS_FILTER, {});
// EXTERNAL MODULE: ./node_modules/@babel/runtime/helpers/assertThisInitialized.js
var assertThisInitialized = __webpack_require__(9);
var assertThisInitialized_default = /*#__PURE__*/__webpack_require__.n(assertThisInitialized);

// EXTERNAL MODULE: external {"this":["wp","htmlEntities"]}
var external_this_wp_htmlEntities_ = __webpack_require__(40);

// EXTERNAL MODULE: external {"this":["wc","components"]}
var external_this_wc_components_ = __webpack_require__(47);

// EXTERNAL MODULE: external {"this":["wc","navigation"]}
var external_this_wc_navigation_ = __webpack_require__(21);

// EXTERNAL MODULE: external {"this":["wc","number"]}
var external_this_wc_number_ = __webpack_require__(142);

// EXTERNAL MODULE: ./client/settings/index.js
var settings = __webpack_require__(25);

// EXTERNAL MODULE: ./client/analytics/components/report-table/index.js + 2 modules
var report_table = __webpack_require__(503);

// CONCATENATED MODULE: ./client/analytics/report/stock/utils.js
/**
 * Determine if a product or variation is in low stock.
 *
 * @param {number} threshold - The number at which stock is determined to be low.
 * @return {boolean} - Whether or not the stock is low.
 */
function isLowStock(status, quantity, threshold) {
  if (!quantity) {
    // Sites that don't do inventory tracking will always return false.
    return false;
  }

  return status && quantity <= threshold === 'instock';
}
// EXTERNAL MODULE: ./client/lib/currency-context.js
var currency_context = __webpack_require__(495);

// CONCATENATED MODULE: ./client/analytics/report/stock/table.js








function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = getPrototypeOf_default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = getPrototypeOf_default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return possibleConstructorReturn_default()(this, result); }; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * External dependencies
 */







/**
 * Internal dependencies
 */




var stockStatuses = Object(settings["g" /* getSetting */])('stockStatuses', {});

var table_StockReportTable = /*#__PURE__*/function (_Component) {
  inherits_default()(StockReportTable, _Component);

  var _super = _createSuper(StockReportTable);

  function StockReportTable() {
    var _this;

    classCallCheck_default()(this, StockReportTable);

    _this = _super.call(this);
    _this.getHeadersContent = _this.getHeadersContent.bind(assertThisInitialized_default()(_this));
    _this.getRowsContent = _this.getRowsContent.bind(assertThisInitialized_default()(_this));
    _this.getSummary = _this.getSummary.bind(assertThisInitialized_default()(_this));
    return _this;
  }

  createClass_default()(StockReportTable, [{
    key: "getHeadersContent",
    value: function getHeadersContent() {
      return [{
        label: Object(external_this_wp_i18n_["__"])('Product / Variation', 'woocommerce-admin'),
        key: 'title',
        required: true,
        isLeftAligned: true,
        isSortable: true
      }, {
        label: Object(external_this_wp_i18n_["__"])('SKU', 'woocommerce-admin'),
        key: 'sku',
        isSortable: true
      }, {
        label: Object(external_this_wp_i18n_["__"])('Status', 'woocommerce-admin'),
        key: 'stock_status',
        isSortable: true,
        defaultSort: true
      }, {
        label: Object(external_this_wp_i18n_["__"])('Stock', 'woocommerce-admin'),
        key: 'stock_quantity',
        isSortable: true
      }];
    }
  }, {
    key: "getRowsContent",
    value: function getRowsContent() {
      var _this2 = this;

      var products = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
      var query = this.props.query;
      var persistedQuery = Object(external_this_wc_navigation_["getPersistedQuery"])(query);
      return products.map(function (product) {
        var id = product.id,
            manageStock = product.manage_stock,
            parentId = product.parent_id,
            sku = product.sku,
            stockQuantity = product.stock_quantity,
            stockStatus = product.stock_status,
            lowStockAmount = product.low_stock_amount;
        var name = Object(external_this_wp_htmlEntities_["decodeEntities"])(product.name);
        var productDetailLink = Object(external_this_wc_navigation_["getNewPath"])(persistedQuery, '/analytics/products', {
          filter: 'single_product',
          products: parentId || id
        });
        var nameLink = Object(external_this_wp_element_["createElement"])(external_this_wc_components_["Link"], {
          href: productDetailLink,
          type: "wc-admin"
        }, name);
        var editProductLink = Object(settings["f" /* getAdminLink */])('post.php?action=edit&post=' + (parentId || id));
        var stockStatusLink = isLowStock(stockStatus, stockQuantity, lowStockAmount) ? Object(external_this_wp_element_["createElement"])(external_this_wc_components_["Link"], {
          href: editProductLink,
          type: "wp-admin"
        }, Object(external_this_wp_i18n_["_x"])('Low', 'Indication of a low quantity', 'woocommerce-admin')) : Object(external_this_wp_element_["createElement"])(external_this_wc_components_["Link"], {
          href: editProductLink,
          type: "wp-admin"
        }, stockStatuses[stockStatus]);
        return [{
          display: nameLink,
          value: name
        }, {
          display: sku,
          value: sku
        }, {
          display: stockStatusLink,
          value: stockStatus
        }, {
          display: manageStock ? Object(external_this_wc_number_["formatValue"])(_this2.context.getCurrencyConfig(), 'number', stockQuantity) : Object(external_this_wp_i18n_["__"])('N/A', 'woocommerce-admin'),
          value: stockQuantity
        }];
      });
    }
  }, {
    key: "getSummary",
    value: function getSummary(totals) {
      var _totals$products = totals.products,
          products = _totals$products === void 0 ? 0 : _totals$products,
          _totals$outofstock = totals.outofstock,
          outofstock = _totals$outofstock === void 0 ? 0 : _totals$outofstock,
          _totals$lowstock = totals.lowstock,
          lowstock = _totals$lowstock === void 0 ? 0 : _totals$lowstock,
          _totals$instock = totals.instock,
          instock = _totals$instock === void 0 ? 0 : _totals$instock,
          _totals$onbackorder = totals.onbackorder,
          onbackorder = _totals$onbackorder === void 0 ? 0 : _totals$onbackorder;
      var currency = this.context.getCurrencyConfig();
      return [{
        label: Object(external_this_wp_i18n_["_n"])('product', 'products', products, 'woocommerce-admin'),
        value: Object(external_this_wc_number_["formatValue"])(currency, 'number', products)
      }, {
        label: Object(external_this_wp_i18n_["__"])('out of stock', 'woocommerce-admin'),
        value: Object(external_this_wc_number_["formatValue"])(currency, 'number', outofstock)
      }, {
        label: Object(external_this_wp_i18n_["__"])('low stock', 'woocommerce-admin'),
        value: Object(external_this_wc_number_["formatValue"])(currency, 'number', lowstock)
      }, {
        label: Object(external_this_wp_i18n_["__"])('on backorder', 'woocommerce-admin'),
        value: Object(external_this_wc_number_["formatValue"])(currency, 'number', onbackorder)
      }, {
        label: Object(external_this_wp_i18n_["__"])('in stock', 'woocommerce-admin'),
        value: Object(external_this_wc_number_["formatValue"])(currency, 'number', instock)
      }];
    }
  }, {
    key: "render",
    value: function render() {
      var _this$props = this.props,
          advancedFilters = _this$props.advancedFilters,
          filters = _this$props.filters,
          query = _this$props.query;
      return Object(external_this_wp_element_["createElement"])(report_table["a" /* default */], {
        endpoint: "stock",
        getHeadersContent: this.getHeadersContent,
        getRowsContent: this.getRowsContent,
        getSummary: this.getSummary,
        summaryFields: ['products', 'outofstock', 'lowstock', 'instock', 'onbackorder'],
        query: query,
        tableQuery: {
          orderby: query.orderby || 'stock_status',
          order: query.order || 'asc',
          type: query.type || 'all'
        },
        title: Object(external_this_wp_i18n_["__"])('Stock', 'woocommerce-admin'),
        filters: filters,
        advancedFilters: advancedFilters
      });
    }
  }]);

  return StockReportTable;
}(external_this_wp_element_["Component"]);

table_StockReportTable.contextType = currency_context["a" /* CurrencyContext */];
/* harmony default export */ var table = (table_StockReportTable);
// EXTERNAL MODULE: ./client/analytics/components/report-filters/index.js
var report_filters = __webpack_require__(502);

// CONCATENATED MODULE: ./client/analytics/report/stock/index.js







function stock_createSuper(Derived) { var hasNativeReflectConstruct = stock_isNativeReflectConstruct(); return function _createSuperInternal() { var Super = getPrototypeOf_default()(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = getPrototypeOf_default()(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return possibleConstructorReturn_default()(this, result); }; }

function stock_isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

/**
 * External dependencies
 */


/**
 * Internal dependencies
 */





var stock_StockReport = /*#__PURE__*/function (_Component) {
  inherits_default()(StockReport, _Component);

  var _super = stock_createSuper(StockReport);

  function StockReport() {
    classCallCheck_default()(this, StockReport);

    return _super.apply(this, arguments);
  }

  createClass_default()(StockReport, [{
    key: "render",
    value: function render() {
      var _this$props = this.props,
          query = _this$props.query,
          path = _this$props.path;
      return Object(external_this_wp_element_["createElement"])(external_this_wp_element_["Fragment"], null, Object(external_this_wp_element_["createElement"])(report_filters["a" /* default */], {
        query: query,
        path: path,
        showDatePicker: showDatePicker,
        filters: config_filters,
        advancedFilters: config_advancedFilters,
        report: "stock"
      }), Object(external_this_wp_element_["createElement"])(table, {
        query: query,
        filters: config_filters,
        advancedFilters: config_advancedFilters
      }));
    }
  }]);

  return StockReport;
}(external_this_wp_element_["Component"]);


stock_StockReport.propTypes = {
  query: prop_types_default.a.object.isRequired
};

/***/ })

}]);