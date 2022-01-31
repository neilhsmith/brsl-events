import {
  createSlice,
  createAsyncThunk,
  createSelector,
  PayloadAction,
} from "@reduxjs/toolkit";
import { RootState } from "../../app/store";
import { ICartItem, ICartState, ISubmitCheckoutRequest } from "./cart.types";
import { ILja } from "features/ljas/ljas.types";
import memoize from "lodash.memoize";
import api from "../../utils/api";
import { toast } from "react-toastify";

import { getLjas } from "../ljas/ljasSlice";

export const submitCheckout = createAsyncThunk(
  "cart/submitCheckout",
  async (request: ISubmitCheckoutRequest, { getState, rejectWithValue }) => {
    const state = getState() as RootState;

    try {
      const response = await api.post("reservation/purchase", {
        token: request,
        items: state.cart.ljas,
      });

      toast.success("Tickets purchased.", {
        position: "top-right",
        autoClose: 5000,
        hideProgressBar: true,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
        progress: undefined,
      });

      return response.data;
    } catch (error: any) {
      toast.error(`Error! ${error.response.data.message}`);
      throw new Error(error.response.data.message);
    }
  }
);

const initialState: ICartState = {
  status: "idle",
  ljas: {},
};

export const cartSlice = createSlice({
  name: "cart",
  initialState,
  reducers: {
    updateCartCount: (state, action: PayloadAction<ICartItem>) => {
      state.ljas[action.payload.ljaId] = action.payload.count;
    },
  },
  extraReducers: (builder) => {
    builder.addCase(getLjas.fulfilled, (state, action) => {
      action.payload.forEach((lja: ILja) => {
        state.ljas[lja.id] = 0;
      });
    });

    builder.addCase(submitCheckout.pending, (state, action) => {
      state.status = "pending";
    });
    builder.addCase(submitCheckout.fulfilled, (state, action) => {
      for (const prop in state.ljas) state.ljas[prop] = 0;
      state.status = "idle";
    });
    builder.addCase(submitCheckout.rejected, (state, action) => {
      state.status = "idle";
    });
  },
});

export default cartSlice.reducer;
export const { updateCartCount } = cartSlice.actions;

export const selectCart = (state: RootState) => state.cart;

export const selectCartStatus = (state: RootState) => state.cart.status;

export const selectCartItems = createSelector(selectCart, (cart) => cart.ljas);

export const selectCartCount = createSelector(selectCart, (cart) => {
  let total = 0;
  for (const prop in cart.ljas) total += cart.ljas[prop];
  return total;
});

export const selectCartCountByLjaId = createSelector(selectCart, (cart) =>
  memoize((ljaId: number) => {
    return cart.ljas[ljaId];
  })
);

export const getCartTotal = createSelector(selectCart, (cart) => {
  let total = 0;

  // TODO: get price from appConfig
  for (const prop in cart.ljas) {
    total += cart.ljas[prop] * 125;
  }

  return total;
});

export const selectCanCheckout = createSelector(selectCart, (cart) => {
  for (const prop in cart.ljas) {
    if (cart.ljas[prop] > 0) {
      return true;
    }
  }
});
