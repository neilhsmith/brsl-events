import React, { ChangeEventHandler, SyntheticEvent } from "react";
import styled from "styled-components";
import { FaBars } from "react-icons/fa";

import { useAppSelector, useAppDispatch } from "../../app/hooks";
import { selectPrepaidCounts } from "../app/appSlice";
import { updateReservation } from "./reservationsSlice";
import { IReservation } from "./reservations.types";
import { ILja } from "../ljas/ljas.types";

const StyledFieldset = styled.fieldset`
  display: flex;
  flex-direction: row;
  background-color: #f8f8f8;
  border-radius: 6px;

  & > div:first-child {
    padding-top: 1.25rem;
    padding-left: 1.25rem;
  }
  & > div:last-child {
    flex: 1;
  }
  @media (max-width: 767px) {
    & > div:first-child {
      padding-top: 0.5rem;
      padding-left: 0.25rem;
    }
  }
`;
interface ItemWrapperProps {
  flex?: boolean;
  fullWidth?: boolean;
}
const ItemWrapper = styled.div`
  padding: 0.25rem 0.5rem;
  flex: ${(props: ItemWrapperProps) => (props.flex ? "1" : "0")};
  flex-basis: ${(props: ItemWrapperProps) =>
    props.fullWidth ? "100%" : "auto"};

  @media all and (max-width: 767px) {
    padding: 0.25rem 0.25rem;
  }
`;
const Body = styled.div`
  display: flex;
  flex-wrap: wrap;
`;
const TextBoxes = styled.div`
  display: flex;
  flex: 1;
`;
const ExtraBoxes = styled.div`
  display: flex;

  & > div {
    min-width: 7.5rem;
  }

  @media (max-width: 767px) {
    width: 100%;

    & > div {
      min-width: 50%;
    }
  }
`;
const TextBox = styled.input`
  width: 100%;
  height: 100%;
  padding: 0.25rem 0.5rem;
`;
const Checkbox = styled.div`
  input {
    margin-right: 0.25rem;
  }
  label {
    margin: 0;
    font-size: 1.5rem;
  }
`;
const Type = styled.div`
  display: flex;
  justify-content: flex-end;
  align-items: center;
  height: 100%;
  font-size: 1.3rem;
`;

interface ReservationItemProps {
  reservation: IReservation;
  lja: ILja;
}

const ReservationItem = ({ reservation, lja }: ReservationItemProps) => {
  const dispatch = useAppDispatch();
  const prepaidCounts = useAppSelector(selectPrepaidCounts);

  let type = "";
  if (lja.role === "brsl_senior")
    type = reservation.order < prepaidCounts.senior ? "Prepaid" : "Purchased";
  else if (lja.role === "brsl_junior")
    type = reservation.order < prepaidCounts.junior ? "Prepaid" : "Purchased";
  else if (lja.role === "brsl_sophomore")
    type =
      reservation.order < prepaidCounts.sophomore ? "Prepaid" : "Purchased";
  else if (lja.role === "brsl_freshman")
    type = reservation.order < prepaidCounts.freshman ? "Prepaid" : "Purchased";

  const handleChange = (event: any) => {
    if (!reservation) return;

    const key = event.target.type === "checkbox" ? "checked" : "value";
    dispatch(
      updateReservation({
        id: reservation.id,
        changes: { [event.target.name]: event.target[key] },
      })
    );
  };

  return (
    <StyledFieldset>
      <ItemWrapper>
        <FaBars size={24} color="#433437" />
      </ItemWrapper>
      <ItemWrapper>
        <Body>
          <TextBoxes>
            <ItemWrapper flex>
              <TextBox
                type="text"
                name="firstName"
                placeholder="First Name"
                value={reservation?.firstName}
                onChange={handleChange}
              />
            </ItemWrapper>
            <ItemWrapper flex>
              <TextBox
                type="text"
                name="lastName"
                placeholder="Last Name"
                value={reservation?.lastName}
                onChange={handleChange}
              />
            </ItemWrapper>
          </TextBoxes>
          <ExtraBoxes>
            <ItemWrapper>
              <Checkbox>
                <input
                  type="checkbox"
                  name="under21"
                  id={`cb-21-${reservation.id}`}
                  defaultChecked={reservation?.under21}
                  onChange={handleChange}
                />
                <label>Under 21</label>
              </Checkbox>
              <Checkbox>
                <input
                  type="checkbox"
                  name="veganMeal"
                  id={`cb-vegan-${reservation.id}`}
                  defaultChecked={reservation?.veganMeal}
                  onChange={handleChange}
                />
                <label>Vegetarian Meal</label>
              </Checkbox>
            </ItemWrapper>
            <ItemWrapper>
              <Type>{type}</Type>
            </ItemWrapper>
          </ExtraBoxes>
        </Body>
      </ItemWrapper>
    </StyledFieldset>
  );
};

export default ReservationItem;
