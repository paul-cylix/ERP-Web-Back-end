"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_views_Categories_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/views/Categories.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/views/Categories.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: "category",
  data: function data() {
    return {
      categoryData: {
        name: "",
        image: ""
      }
    };
  },
  methods: {
    attachImage: function attachImage() {
      this.categoryData.image = this.$refs.newCategoryImage.files[0];
      var reader = new FileReader();
      reader.addEventListener('load', function () {
        this.$refs.newCategoryImageDisplay.src = reader.result;
      }.bind(this), false);
      reader.readAsDataURL(this.categoryData.image);
    },
    createCategory: function createCategory() {
      console.log("form submitted");
    }
  }
});

/***/ }),

/***/ "./resources/js/views/Categories.vue":
/*!*******************************************!*\
  !*** ./resources/js/views/Categories.vue ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Categories_vue_vue_type_template_id_53f0967b___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Categories.vue?vue&type=template&id=53f0967b& */ "./resources/js/views/Categories.vue?vue&type=template&id=53f0967b&");
/* harmony import */ var _Categories_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Categories.vue?vue&type=script&lang=js& */ "./resources/js/views/Categories.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _Categories_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Categories_vue_vue_type_template_id_53f0967b___WEBPACK_IMPORTED_MODULE_0__.render,
  _Categories_vue_vue_type_template_id_53f0967b___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/Categories.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/views/Categories.vue?vue&type=script&lang=js&":
/*!********************************************************************!*\
  !*** ./resources/js/views/Categories.vue?vue&type=script&lang=js& ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Categories_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Categories.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/views/Categories.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Categories_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/Categories.vue?vue&type=template&id=53f0967b&":
/*!**************************************************************************!*\
  !*** ./resources/js/views/Categories.vue?vue&type=template&id=53f0967b& ***!
  \**************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Categories_vue_vue_type_template_id_53f0967b___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Categories_vue_vue_type_template_id_53f0967b___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Categories_vue_vue_type_template_id_53f0967b___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Categories.vue?vue&type=template&id=53f0967b& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/views/Categories.vue?vue&type=template&id=53f0967b&");


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/views/Categories.vue?vue&type=template&id=53f0967b&":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/views/Categories.vue?vue&type=template&id=53f0967b& ***!
  \*****************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "content-wrapper" }, [
    _vm._m(0),
    _vm._v(" "),
    _c("section", { staticClass: "content" }, [
      _c("div", { staticClass: "container-fluid" }, [
        _c("div", { staticClass: "card" }, [
          _c("div", { staticClass: "card-header" }, [
            _vm._m(1),
            _vm._v(" "),
            _c(
              "div",
              {
                staticClass: "modal fade",
                attrs: {
                  id: "exampleModal",
                  tabindex: "-1",
                  role: "dialog",
                  "aria-labelledby": "exampleModalLabel",
                  "aria-hidden": "true"
                }
              },
              [
                _c(
                  "div",
                  { staticClass: "modal-dialog", attrs: { role: "document" } },
                  [
                    _c("div", { staticClass: "modal-content" }, [
                      _vm._m(2),
                      _vm._v(" "),
                      _c("div", { staticClass: "modal-body" }, [
                        _c(
                          "form",
                          {
                            on: {
                              submit: function($event) {
                                $event.preventDefault()
                                return _vm.createCategory.apply(null, arguments)
                              }
                            }
                          },
                          [
                            _c("div", { staticClass: "form-group" }, [
                              _c(
                                "label",
                                { attrs: { for: "exampleInputtext1" } },
                                [_vm._v("Email address")]
                              ),
                              _vm._v(" "),
                              _c("input", {
                                directives: [
                                  {
                                    name: "model",
                                    rawName: "v-model",
                                    value: _vm.categoryData.name,
                                    expression: "categoryData.name"
                                  }
                                ],
                                staticClass: "form-control",
                                attrs: {
                                  type: "text",
                                  id: "name",
                                  "aria-describedby": "textHelp",
                                  placeholder: "Enter text"
                                },
                                domProps: { value: _vm.categoryData.name },
                                on: {
                                  input: function($event) {
                                    if ($event.target.composing) {
                                      return
                                    }
                                    _vm.$set(
                                      _vm.categoryData,
                                      "name",
                                      $event.target.value
                                    )
                                  }
                                }
                              }),
                              _vm._v(" "),
                              _c(
                                "small",
                                {
                                  staticClass: "form-text text-muted",
                                  attrs: { id: "textHelp" }
                                },
                                [
                                  _vm._v(
                                    "We'll never share your text\n                                                with anyone else."
                                  )
                                ]
                              )
                            ]),
                            _vm._v(" "),
                            _c("div", { staticClass: "form-group" }, [
                              _c(
                                "label",
                                { attrs: { for: "exampleFormControlFile1" } },
                                [_vm._v("Example file input")]
                              ),
                              _vm._v(" "),
                              _c("div", [
                                _c("img", {
                                  ref: "newCategoryImageDisplay",
                                  staticStyle: { width: "150px" },
                                  attrs: { src: "", alt: "", srcset: "" }
                                })
                              ]),
                              _vm._v(" "),
                              _c("input", {
                                ref: "newCategoryImage",
                                staticClass: "form-control-file",
                                attrs: { type: "file", id: "image" },
                                on: { change: _vm.attachImage }
                              })
                            ]),
                            _vm._v(" "),
                            _c(
                              "button",
                              {
                                staticClass: "btn btn-primary btn-sm",
                                attrs: { type: "submit" }
                              },
                              [
                                _vm._v(
                                  "\n                                            Submit\n                                        "
                                )
                              ]
                            )
                          ]
                        )
                      ]),
                      _vm._v(" "),
                      _vm._m(3)
                    ])
                  ]
                )
              ]
            )
          ]),
          _vm._v(" "),
          _vm._m(4),
          _vm._v(" "),
          _vm._m(5)
        ])
      ])
    ])
  ])
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "content-header" }, [
      _c("div", { staticClass: "container-fluid" }, [
        _c("div", { staticClass: "row mb-2" }, [
          _c("div", { staticClass: "col-sm-6" }, [
            _c("h1", { staticClass: "m-0" }, [_vm._v("Categories")])
          ]),
          _vm._v(" "),
          _c("div", { staticClass: "col-sm-6" }, [
            _c("ol", { staticClass: "breadcrumb float-sm-right" }, [
              _c("li", { staticClass: "breadcrumb-item" }, [
                _c("a", { attrs: { href: "#" } }, [_vm._v("Home")])
              ]),
              _vm._v(" "),
              _c("li", { staticClass: "breadcrumb-item active" }, [
                _vm._v(
                  "\n                            Categories v1\n                        "
                )
              ])
            ])
          ])
        ])
      ])
    ])
  },
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "d-flex justify-content-between" }, [
      _c("div", { staticClass: "m-0 p-0" }, [_vm._v("Test")]),
      _vm._v(" "),
      _c(
        "button",
        {
          staticClass: "btn btn-primary btn-sm",
          attrs: {
            type: "button",
            "data-toggle": "modal",
            "data-target": "#exampleModal"
          }
        },
        [
          _vm._v(
            "\n                            Create new\n                        "
          )
        ]
      )
    ])
  },
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "modal-header" }, [
      _c(
        "h5",
        { staticClass: "modal-title", attrs: { id: "exampleModalLabel" } },
        [
          _vm._v(
            "\n                                        Modal title\n                                    "
          )
        ]
      ),
      _vm._v(" "),
      _c(
        "button",
        {
          staticClass: "close",
          attrs: {
            type: "button",
            "data-dismiss": "modal",
            "aria-label": "Close"
          }
        },
        [_c("span", { attrs: { "aria-hidden": "true" } }, [_vm._v("×")])]
      )
    ])
  },
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "modal-footer" }, [
      _c(
        "button",
        {
          staticClass: "btn btn-secondary",
          attrs: { type: "button", "data-dismiss": "modal" }
        },
        [
          _vm._v(
            "\n                                        Close\n                                    "
          )
        ]
      ),
      _vm._v(" "),
      _c(
        "button",
        { staticClass: "btn btn-primary", attrs: { type: "submit" } },
        [
          _vm._v(
            "\n                                        Save changes\n                                    "
          )
        ]
      )
    ])
  },
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "card-body p-0" }, [
      _c("table", { staticClass: "table table-bordered table-sm" }, [
        _c("thead", [
          _c("tr", [
            _c("th", { staticStyle: { width: "10px" } }, [_vm._v("#")]),
            _vm._v(" "),
            _c("th", [_vm._v("Task")]),
            _vm._v(" "),
            _c("th", [_vm._v("Progress")]),
            _vm._v(" "),
            _c("th", { staticStyle: { width: "40px" } }, [_vm._v("Label")])
          ])
        ]),
        _vm._v(" "),
        _c("tbody", [
          _c("tr", [
            _c("td", [_vm._v("1.")]),
            _vm._v(" "),
            _c("td", [_vm._v("Update software")]),
            _vm._v(" "),
            _c("td", [
              _c("div", { staticClass: "progress progress-xs" }, [
                _c("div", {
                  staticClass: "progress-bar progress-bar-danger",
                  staticStyle: { width: "55%" }
                })
              ])
            ]),
            _vm._v(" "),
            _c("td", [
              _c("span", { staticClass: "badge bg-danger" }, [_vm._v("55%")])
            ])
          ]),
          _vm._v(" "),
          _c("tr", [
            _c("td", [_vm._v("2.")]),
            _vm._v(" "),
            _c("td", [_vm._v("Clean database")]),
            _vm._v(" "),
            _c("td", [
              _c("div", { staticClass: "progress progress-xs" }, [
                _c("div", {
                  staticClass: "progress-bar bg-warning",
                  staticStyle: { width: "70%" }
                })
              ])
            ]),
            _vm._v(" "),
            _c("td", [
              _c("span", { staticClass: "badge bg-warning" }, [_vm._v("70%")])
            ])
          ]),
          _vm._v(" "),
          _c("tr", [
            _c("td", [_vm._v("3.")]),
            _vm._v(" "),
            _c("td", [_vm._v("Cron job running")]),
            _vm._v(" "),
            _c("td", [
              _c(
                "div",
                { staticClass: "progress progress-xs progress-striped active" },
                [
                  _c("div", {
                    staticClass: "progress-bar bg-primary",
                    staticStyle: { width: "30%" }
                  })
                ]
              )
            ]),
            _vm._v(" "),
            _c("td", [
              _c("span", { staticClass: "badge bg-primary" }, [_vm._v("30%")])
            ])
          ]),
          _vm._v(" "),
          _c("tr", [
            _c("td", [_vm._v("4.")]),
            _vm._v(" "),
            _c("td", [_vm._v("Fix and squish bugs")]),
            _vm._v(" "),
            _c("td", [
              _c(
                "div",
                { staticClass: "progress progress-xs progress-striped active" },
                [
                  _c("div", {
                    staticClass: "progress-bar bg-success",
                    staticStyle: { width: "90%" }
                  })
                ]
              )
            ]),
            _vm._v(" "),
            _c("td", [
              _c("span", { staticClass: "badge bg-success" }, [_vm._v("90%")])
            ])
          ])
        ])
      ])
    ])
  },
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "card-footer clearfix" }, [
      _c("ul", { staticClass: "pagination pagination-sm m-0 float-right" }, [
        _c("li", { staticClass: "page-item" }, [
          _c("a", { staticClass: "page-link", attrs: { href: "#" } }, [
            _vm._v("«")
          ])
        ]),
        _vm._v(" "),
        _c("li", { staticClass: "page-item" }, [
          _c("a", { staticClass: "page-link", attrs: { href: "#" } }, [
            _vm._v("1")
          ])
        ]),
        _vm._v(" "),
        _c("li", { staticClass: "page-item" }, [
          _c("a", { staticClass: "page-link", attrs: { href: "#" } }, [
            _vm._v("2")
          ])
        ]),
        _vm._v(" "),
        _c("li", { staticClass: "page-item" }, [
          _c("a", { staticClass: "page-link", attrs: { href: "#" } }, [
            _vm._v("3")
          ])
        ]),
        _vm._v(" "),
        _c("li", { staticClass: "page-item" }, [
          _c("a", { staticClass: "page-link", attrs: { href: "#" } }, [
            _vm._v("»")
          ])
        ])
      ])
    ])
  }
]
render._withStripped = true



/***/ })

}]);