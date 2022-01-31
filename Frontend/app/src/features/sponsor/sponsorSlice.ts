import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import { RootState } from "../../app/store";
import { ISponsorState } from "./sponsor.types";

import api from "../../utils/api";

export const getSponsor = createAsyncThunk("sponsor/getSponsor", async () => {
  let response = await api.get("/sponsor");
  return response.data;
});

const initialState: ISponsorState = {
  status: "idle",
  entity: null,
};

export const sponsorSlice = createSlice({
  name: "sponsor",
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder.addCase(getSponsor.pending, (state, action) => {
      state.status = "pending";
    });
    builder.addCase(getSponsor.fulfilled, (state, action) => {
      state.status = "fulfilled";
      state.entity = action.payload;
    });
  },
});

export default sponsorSlice.reducer;
