import {
  createEntityAdapter,
  createSlice,
  createAsyncThunk,
  createSelector,
} from "@reduxjs/toolkit";
import { RootState } from "../../app/store";
import { IReservation } from "./reservations.types";
import memoize from "lodash.memoize";
import { toast } from "react-toastify";

import api from "../../utils/api";
import { submitCheckout } from "../cart/cartSlice";
import { relinquishLjaSeats } from "../ljas/ljasSlice";

export const getReservations = createAsyncThunk(
  "reservations/getReservations",
  async (ljaIds: number[]) => {
    let response = await api.post("/reservation", {
      ljaIds,
    });
    return response.data;
  }
);

interface ReorderReservationRequest {
  reservationId: number;
  index: number;
}

export const reorderReservation = createAsyncThunk(
  "reservations/reorder",
  async (request: ReorderReservationRequest, thunkApi) => {
    const state = thunkApi.getState() as RootState;
    const reservations = selectReservations(state);

    const reservation = reservations.find(
      (res: any) => res.id === request.reservationId.toString()
    );
    if (!reservation) return null;

    const ljaReservations = reservations
      .filter(
        (res: IReservation) =>
          res.ljaId === reservation.ljaId && res.id !== reservation.id
      )
      .sort((a, b) => (a.order > b.order ? 1 : -1));

    ljaReservations.splice(request.index, 0, reservation);

    const updatedReservations: IReservation[] = ljaReservations.map(
      (res, idx) => {
        let updated = { ...res };
        updated.order = idx;
        return updated;
      }
    );

    return updatedReservations;
  }
);

export const updateReservations = createAsyncThunk(
  "reservations/update",
  async (_, thunkApi) => {
    const state = thunkApi.getState() as RootState;

    let response = await api.post("/reservation/update", {
      reservations: state.reservations.entities,
    });

    toast.success("Reservations updated.", {
      position: "top-right",
      autoClose: 5000,
      hideProgressBar: true,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      progress: undefined,
    });

    return response.data;
  }
);

const reservationsAdapter = createEntityAdapter<IReservation>({});

const reservationsSlice = createSlice({
  name: "reservations",
  initialState: reservationsAdapter.getInitialState(),
  reducers: {
    updateOne: reservationsAdapter.updateOne,
  },
  extraReducers: (builder) => {
    builder.addCase(getReservations.fulfilled, (state, action) => {
      reservationsAdapter.setAll(state, action.payload);
    });

    builder.addCase(reorderReservation.fulfilled, (state, action) => {
      action.payload?.forEach((res, idx) => {
        reservationsAdapter.updateOne(state, {
          id: res.id,
          changes: {
            order: idx,
          },
        });
      });
    });

    builder.addCase(submitCheckout.fulfilled, (state, action) => {
      reservationsAdapter.addMany(state, action.payload);
    });

    builder.addCase(relinquishLjaSeats.fulfilled, (state, action) => {
      reservationsAdapter.removeMany(state, action.payload.reservationIds);
    });
  },
});

export default reservationsSlice.reducer;

export const { updateOne: updateReservation } = reservationsSlice.actions;

export const {
  selectAll: selectReservations,
  selectById: selectReservationById,
} = reservationsAdapter.getSelectors<RootState>((state) => state.reservations);

export const selectReservationsByLjaId = createSelector(
  selectReservations,
  (reservations) => {
    return memoize((ljaId: number) =>
      reservations.filter((r) => r.ljaId === ljaId)
    );
  }
);
