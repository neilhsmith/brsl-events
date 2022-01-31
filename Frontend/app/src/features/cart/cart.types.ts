export interface ICartItem {
  ljaId: number;
  count: number;
}

export interface ISubmitCheckoutRequest {
  id: string;
}

export interface ICartState {
  status: "idle" | "pending";
  ljas: { [key: string]: number };
}
