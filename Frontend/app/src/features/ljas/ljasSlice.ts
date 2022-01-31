import {
  createSlice,
  createAsyncThunk,
  createSelector,
  PayloadAction,
} from "@reduxjs/toolkit";
import { RootState } from "../../app/store";
import { ILjasState, ILja } from "./ljas.types";
import {
  getReservations,
  selectReservations,
} from "../reservations/reservationsSlice";
import memoize from "lodash.memoize";
import { toast } from "react-toastify";

import api from "../../utils/api";
import { IReservation } from "../reservations/reservations.types";

export const getLjas = createAsyncThunk(
  "ljas/getLjas",
  async (_, { dispatch }) => {
    let response = await api.get("/ljas");
    let ljaIds = response.data.map((lja: ILja) => lja.id);
    dispatch(getReservations(ljaIds));
    return response.data;
  }
);

export const updateLjas = createAsyncThunk(
  "ljas/update",
  async (_, thunkApi) => {
    const state = thunkApi.getState() as RootState;

    let response = await api.post("/ljas/update", {
      ljas: state.ljas.entities,
    });
    return response.data;
  }
);

export const updateAcknowledgesResponsibility = createAsyncThunk(
  "ljas/updateAcknowledgesResponsibility",
  async (ljaId: number, thunkApi) => {
    const state = thunkApi.getState() as RootState;
    const lja = state.ljas.entities?.find((lja) => lja.id === ljaId);

    try {
      const response = await api.post("/ljas/update", {
        ljas: state.ljas.entities?.map((lja) => {
          if (lja.id === ljaId)
            return { ...lja, acknowledgesResponsibility: true };

          return lja;
        }),
      });

      return ljaId;
    } catch (error: any) {
      toast.error(`Error! ${error.response.data.message}`);
    }
  }
);

export const claimSeats = createAsyncThunk(
  "ljas/claimSeats",
  async (ljaId: number, thunkApi) => {
    const state = thunkApi.getState() as RootState;
    const lja = state.ljas.entities?.find((lja) => lja.id === ljaId);

    try {
      const response = await api.post("/ljas/update", {
        ljas: state.ljas.entities?.map((lja) => {
          if (lja.id === ljaId) return { ...lja, acknowledgesRelinquish: true };

          return lja;
        }),
      });

      return ljaId;
    } catch (error: any) {
      toast.error(`Error! ${error.response.data.message}`);
    }
  }
);

export const relinquishLjaSeats = createAsyncThunk(
  "ljas/relinquishSeats",
  async (ljaId: number, thunkApi) => {
    try {
      const response = await api.post("/ljas/relinquish_seats", {
        ljaId,
      });

      const state = thunkApi.getState() as RootState;
      const reservations = selectReservations(state);
      const reservationIds = reservations.map((r: IReservation) => {
        if (r.ljaId === ljaId) {
          return r.id;
        }
      });

      return {
        ljaId,
        reservationIds,
      };
    } catch (error: any) {
      toast.error(`Error! ${error.response.data.message}`);
    }
  }
);

const initialState: ILjasState = {
  status: "idle",
  entities: null,
};

interface IUpdateNotePayload {
  ljaId: number;
  value: string;
}

export const ljasSlice = createSlice({
  name: "ljas",
  initialState,
  reducers: {
    updateNote: (state, action: PayloadAction<IUpdateNotePayload>) => {
      const lja = state.entities?.find(
        (lja) => lja.id === action.payload.ljaId
      );
      if (lja) lja.notes = action.payload.value;
    },
  },
  extraReducers: (builder) => {
    builder.addCase(getLjas.pending, (state, action) => {
      state.status = "pending";
    });
    builder.addCase(getLjas.fulfilled, (state, action) => {
      state.status = "fulfilled";
      state.entities = action.payload;
    });

    builder.addCase(claimSeats.fulfilled, (state, action) => {
      const lja = state.entities?.find((lja) => lja.id === action.payload);
      if (lja) lja.acknowledgesRelinquish = true;
    });

    builder.addCase(
      updateAcknowledgesResponsibility.fulfilled,
      (state, action) => {
        const lja = state.entities?.find((lja) => lja.id === action.payload);
        if (lja) lja.acknowledgesResponsibility = true;
      }
    );

    builder.addCase(relinquishLjaSeats.fulfilled, (state, action) => {
      const lja = state.entities?.find(
        (lja) => lja.id === action.payload.ljaId
      );
      if (lja) {
        lja.acknowledgesRelinquish = true;
        lja.didRelinquish = true;
      }
    });
  },
});

export default ljasSlice.reducer;
export const { updateNote } = ljasSlice.actions;

export const selectLjas = (state: RootState) => {
  const seniors = state.ljas.entities?.filter(
    (lja) => lja.role === "brsl_senior"
  );
  const juniors = state.ljas.entities?.filter(
    (lja) => lja.role === "brsl_junior"
  );
  const sophomores = state.ljas.entities?.filter(
    (lja) => lja.role === "brsl_sophomore"
  );
  const freshman = state.ljas.entities?.filter(
    (lja) => lja.role === "brsl_freshman"
  );

  let result: any[] = [];

  if (seniors) result = seniors;
  if (juniors) result = result.concat(juniors);
  if (sophomores) result = result.concat(sophomores);
  if (freshman) result = result.concat(freshman);

  return result;
};

export const selectNotesByLjaId = createSelector(selectLjas, (ljas) =>
  memoize((ljaId) => {
    return ljas?.find((l) => l.id === ljaId)?.notes;
  })
);
