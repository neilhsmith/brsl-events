import {
  createSlice,
  createAsyncThunk,
  createSelector,
} from "@reduxjs/toolkit";
import { RootState } from "../../app/store";
import { IAppState } from "./app.types";
import memoize from "lodash.memoize";

import { updateNote } from "../ljas/ljasSlice";
import {
  updateReservations,
  reorderReservation,
} from "../reservations/reservationsSlice";

import api from "../../utils/api";

// Action Creators / Thunks
export const getAppConfig = createAsyncThunk("app/getAppConfig", async () => {
  let response = await api.get("/get_app_config");
  return response.data;
});

const initialState: IAppState = {
  status: "idle",
  canSave: false,
};

export const appSlice = createSlice({
  name: "app",
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder.addCase(getAppConfig.pending, (state, action) => {
      state.status = "pending";
    });
    builder.addCase(getAppConfig.fulfilled, (state, action) => {
      state.status = "fulfilled";
      state.config = action.payload;
    });

    builder.addCase("reservations/updateOne", (state, action) => {
      state.canSave = true;
    });
    builder.addCase(updateReservations.fulfilled, (state, action) => {
      state.canSave = false;
    });
    builder.addCase(reorderReservation.fulfilled, (state, action) => {
      state.canSave = true;
    });

    builder.addCase("ljas/updateNote", (state, action) => {
      state.canSave = true;
    });
  },
});

export default appSlice.reducer;

export const selectApp = (state: RootState) => state.app;

export const selectCanSave = createSelector(selectApp, (app) => app.canSave);

export const selectStripeKey = createSelector(
  selectApp,
  (app) => app.config?.stripeKey
);

export const selectPurchasableCount = createSelector(
  selectApp,
  (app) => app.config?.purchasableCount
);

export const selectSeatCountByRole = createSelector(selectApp, (app) =>
  memoize((role: string) => {
    if (role === "brsl_senior") return app.config?.seniorPrepaidSeats;
    else if (role === "brsl_junior") return app.config?.juniorPrepaidSeats;
    else if (role === "brsl_sophomore")
      return app.config?.sophomorePrepaidSeats;
    else if (role === "brsl_freshman") return app.config?.freshmanPrepaidSeats;

    return null;
  })
);

export const selectRoleEnabled = createSelector(selectApp, (app) =>
  memoize((role: string) => {
    if (role === "brsl_senior") return app.config?.seniorEnabled || false;
    else if (role === "brsl_junior") return app.config?.juniorEnabled || false;
    else if (role === "brsl_sophomore")
      return app.config?.sophomoreEnabled || false;
    else if (role === "brsl_freshman")
      return app.config?.freshmanEnabled || false;

    return false;
  })
);

export const selectPrepaidCounts = createSelector(selectApp, (app) => {
  return {
    senior: app.config?.seniorPrepaidSeats || 0,
    junior: app.config?.juniorPrepaidSeats || 0,
    sophomore: app.config?.sophomorePrepaidSeats || 0,
    freshman: app.config?.freshmanPrepaidSeats || 0,
  };
});

export const selectCanRelinquishByRole = createSelector(selectApp, (app) =>
  memoize((role: string) => {
    if (role === "brsl_senior") return app.config?.seniorCanRelinquish;
    else if (role === "brsl_junior") return app.config?.juniorCanRelinquish;
    else if (role === "brsl_sophomore")
      return app.config?.sophomoreCanRelinquish;
    else if (role === "brsl_freshman") return app.config?.freshmanCanRelinquish;

    return false;
  })
);

export const selectRelinquishDateByRole = createSelector(selectApp, (app) =>
  memoize((role: string) => {
    if (role === "brsl_senior") return app.config?.seniorRelinquishDate;
    else if (role === "brsl_junior") return app.config?.juniorRelinquishDate;
    else if (role === "brsl_sophomore")
      return app.config?.sophomoreRelinquishDate;
    else if (role === "brsl_freshman")
      return app.config?.freshmanRelinquishDate;

    return null;
  })
);
