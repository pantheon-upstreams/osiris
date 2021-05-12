window.sessionStorage.clear();
var session = window.sessionStorage;


class DashboardPage {

  constructor() {
    const { modules, versions } = JSON.parse(document.querySelector("script[type=\"application/json\"][data-settings=\"dashboard-settings\"]").textContent);
    console.log("destructured:", Object.keys(modules), versions);
    session.setItem('modules', JSON.stringify(Object.keys(modules)));
    session.setItem('versions', JSON.stringify(versions));
  }

  get properties() {
    return {
      columns: this.columns,
      dataSource: this.dataSource,
      appearance: {
        alternationCount: 2,
        align: "center",
        verticalAlign: "center",
      },
    };
  }

  get modules() {
    return JSON.parse(session.getItem('modules'));
  }

  get versions() {
    return JSON.parse(session.getItem('versions'));
  }

  get columns() {
    var toReturn = [];
    toReturn.push(new Smart.Grid.Column({ label: "title", dataField: "title" }));
    for (const col in this.versions) { toReturn.push(new Smart.Grid.Column({
      label: col,
      dataField: col,
      align: "center",
      verticalAlign: "center",
    })); }
    return toReturn;
  }

  get dataSource() {
    return new Smart.DataAdapter({
      dataSource: this.dataSourceRows,
    });
  }

  get dataSourceRows() {
    var toReturn = [];

    for(var mod in this.modules) {
      console.log("MOD", this.modules[mod]);

      toReturn.push(Object.assign({}, { name: this.modules[mod], title: this.modules[mod] }, this.emptyRow));
    }
    return toReturn;
  }

  get emptyRow() {
    var toReturn = {};
    for(const version in this.versions) {
      toReturn[version] = "❔";
    }
    return toReturn;
  }

  get versionKeys() {
    return Object.keys(this.versions);
  }

  static formatFunction(settings) {
    console.log(settings);
  }

  static whenReadyHandler() {
    console.log("Ready!");
    const versions = JSON.parse(session.getItem('versions'));
    for (const version in versions) {
      var cachebuster = Math.round(new Date().getTime() / 1000),
        myFetchUrl = "//".concat(versions[version], "&cachebuster=", cachebuster)
      fetch(myFetchUrl, {
        headers: {
          'Accept': 'application/json',
        }
      })
        .then(res => res.json())
        .then(ajaxData => DashboardPage.handleResponse(ajaxData))
        .catch(e => {
          console.log("There was a problem receiving the JSON from the instance:", e);
          //TODO: better error handling
        });
    }
  }

  static getRowIdFromModuleName(moduleName) {
    return JSON.parse(session.getItem('modules')).findIndex( (element) => element == moduleName);
  }

  static handleResponse(ajaxData) {
    console.log("AjaxData", ajaxData);
    const grid = document.querySelector('smart-grid');
    grid.beginUpdate()
    for (const module in ajaxData['modules']) {
      console.log("module data", DashboardPage.getRowIdFromModuleName(module), "column_".concat(ajaxData['version']));
      let cell = grid.rows[DashboardPage.getRowIdFromModuleName(module)].getCell(ajaxData['version']) ?? null;
      if (cell) {
        cell.element.innerHTML  = ajaxData['modules'][module];
        cell.element.setAttribute("align", "center");
        cell.element.setAttribute("verticalAlign", "center");

      }
    }
    grid.endUpdate();
    grid.refreshView();
  }

}

window.onload = () => {
  window.Smart('#grid', DashboardPage);
  setTimeout(DashboardPage.whenReadyHandler, 2000);
}



