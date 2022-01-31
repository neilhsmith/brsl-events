import React from "react";
import styled from "styled-components";
import { DragDropContext } from "react-beautiful-dnd";
import {
  Accordion as AccessibleAccordion,
  AccordionItem,
  AccordionItemHeading,
  AccordionItemButton,
  AccordionItemPanel,
} from "react-accessible-accordion";
import ReservationList from "../reservations/ReservationList";

import { useAppDispatch, useAppSelector } from "../../app/hooks";
import { selectLjas } from "./ljasSlice";
import { reorderReservation } from "../reservations/reservationsSlice";

const StyledAccordion = styled(AccessibleAccordion)`
  margin-bottom: 3em;

  .accordion {
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 2px;
  }

  .accordion__item + .accordion__item {
    border-top: 1px solid rgba(0, 0, 0, 0.1);
  }

  .accordion__button {
    background-color: #f4f4f4;
    color: #444;
    cursor: pointer;
    padding: 18px;
    font-size: 1.8rem;
    font-weight: 700;
    width: 100%;
    text-align: left;
    border: none;
    user-select: none;

    &:hover {
      background-color: #ddd;
    }
  }

  .accordion__button:before {
    display: inline-block;
    content: "";
    height: 10px;
    width: 10px;
    margin-right: 12px;
    border-bottom: 2px solid currentColor;
    border-right: 2px solid currentColor;
    transform: rotate(-45deg);
  }
  .accordion__button[aria-expanded="true"]::before,
  .accordion__button[aria-selected="true"]::before {
    transform: rotate(45deg);
  }

  .accordion__panel {
    background: #fff;
    animation: fadein 0.35s ease-in;
  }
`;

const LjaAccordion = () => {
  const dispatch = useAppDispatch();

  const ljas = useAppSelector(selectLjas);

  const onDragEnd = (result: any) => {
    if (!result.destination) return;

    // dont allow if we're not dropping within the same reservation form
    if (result.destination.droppableId !== result.source.droppableId) return;

    const index = result.destination.index;
    const reservationId = parseInt(result.draggableId);

    // find the dropped reservation in state with reservationId. increment all
    // reservations' order with the same ljaId by 1 where the reservation's order
    // is greater than droppedIdx. then set the dropped reservation's order to
    // droppedIdx

    dispatch(
      reorderReservation({
        reservationId,
        index,
      })
    );
  };

  return (
    <DragDropContext onDragEnd={onDragEnd}>
      <StyledAccordion allowMultipleExpanded={true} allowZeroExpanded={true}>
        {ljas?.map((lja) => (
          <AccordionItem key={lja.id}>
            <AccordionItemHeading>
              <AccordionItemButton>{`${lja.firstName} ${lja.lastName}`}</AccordionItemButton>
            </AccordionItemHeading>
            <AccordionItemPanel>
              <ReservationList lja={lja} />
            </AccordionItemPanel>
          </AccordionItem>
        ))}
      </StyledAccordion>
    </DragDropContext>
  );
};

export default LjaAccordion;
