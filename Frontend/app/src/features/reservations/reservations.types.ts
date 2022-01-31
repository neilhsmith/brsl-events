export interface IReservation {
  id: number;
  ljaId: number;
  createdAt: Date;
  order: number;
  firstName: string;
  lastName: string;
  under21: boolean;
  veganMeal: boolean;
}

export interface IReservationsState {
  status: "idle" | "pending" | "fulfilled";
  entities: IReservation[] | null;
}
