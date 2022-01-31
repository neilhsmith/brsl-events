import {
  combineReducers,
  configureStore,
  AnyAction,
  Action,
} from "@reduxjs/toolkit";
import { ThunkAction } from "redux-thunk";
import logger from "redux-logger";

import appReducer from "../features/app/appSlice";
import cartReducer from "../features/cart/cartSlice";
import sponsorReducer from "../features/sponsor/sponsorSlice";
import ljasReducer from "../features/ljas/ljasSlice";
import reservationsReducer from "../features/reservations/reservationsSlice";

const reducers = combineReducers({
  app: appReducer,
  cart: cartReducer,
  sponsor: sponsorReducer,
  ljas: ljasReducer,
  reservations: reservationsReducer,
});

const store = configureStore({
  reducer: reducers,
  middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(logger),
  devTools: true,
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
export type AppThunk<ReturnType = void> = ThunkAction<
  ReturnType,
  RootState,
  unknown,
  AnyAction
>;

export default store;
