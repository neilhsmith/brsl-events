import * as React from "react";
import * as ReactDOM from "react-dom";
import App from "./app/App";

import store from "./app/store";

import { getAppConfig } from "./features/app/appSlice";
import { getSponsor } from "./features/sponsor/sponsorSlice";
import { getLjas } from "./features/ljas/ljasSlice";

store.dispatch(getAppConfig());
store.dispatch(getSponsor());
store.dispatch(getLjas());

ReactDOM.render(<App />, document.getElementById("reservations-app"));
