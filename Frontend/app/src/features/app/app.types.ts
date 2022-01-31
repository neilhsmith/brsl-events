export interface IAppConfig {
  purchasableCount: number;
  totalAvailableSeats: number | null;
  additionalPurchasableSeats: number | null;
  seniorPrepaidSeats: number | null;
  juniorPrepaidSeats: number | null;
  sophomorePrepaidSeats: number | null;
  freshmanPrepaidSeats: number | null;
  seniorEnabled: boolean;
  juniorEnabled: boolean;
  sophomoreEnabled: boolean;
  freshmanEnabled: boolean;
  seniorCanRelinquish: boolean;
  juniorCanRelinquish: boolean;
  sophomoreCanRelinquish: boolean;
  freshmanCanRelinquish: boolean;
  seniorEnableDate: Date | null;
  juniorEnableDate: Date | null;
  sophomoreEnableDate: Date | null;
  freshmanEnableDate: Date | null;
  seniorRelinquishDate: Date | null;
  juniorRelinquishDate: Date | null;
  sophomoreRelinquishDate: Date | null;
  freshmanRelinquishDate: Date | null;
  stripeKey: string;
}

export interface IAppState {
  status: "idle" | "pending" | "fulfilled";
  canSave: boolean;
  config?: IAppConfig;
}
