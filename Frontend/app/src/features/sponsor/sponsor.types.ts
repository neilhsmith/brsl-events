export interface ISponsor {
  id: number;
  firstName: string;
  lastName: string;
}

export interface ISponsorState {
  status: "idle" | "pending" | "fulfilled";
  entity: ISponsor | null;
}
