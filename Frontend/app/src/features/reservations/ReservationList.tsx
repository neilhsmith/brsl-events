import React from "react";
import styled from "styled-components";
import { Droppable, Draggable } from "react-beautiful-dnd";

import ReservationItem from "./ReservationItem";
import DisabledRoleNotification from "../ljas/DisabledRoleNotification";
import AcknowledgeResponsibility from "../ljas/AcknowledgeResponsibility";

import { useAppSelector, useAppDispatch } from "../../app/hooks";
import { selectReservationsByLjaId } from "./reservationsSlice";
import {
  relinquishLjaSeats,
  selectNotesByLjaId,
  updateNote,
} from "../ljas/ljasSlice";
import {
  selectRoleEnabled,
  selectSeatCountByRole,
  selectCanRelinquishByRole,
} from "../app/appSlice";
import { ILja } from "../ljas/ljas.types";
import RelinquishSeats from "../ljas/RelinquishSeats";
import RelinquishedNotification from "../ljas/RelinquishedNotification";

const Wrapper = styled.div`
  position: relative;
`;
const DisabledBackground = styled.div`
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: ;
  display: flex;
  justify-content: center;
  align-items: flex-start;

  background: ${(props: any) =>
    props.primary ? "#ffffff" : "rgba(255, 255, 255, .9)"};
`;
const Heading = styled.h4`
  margin-top: 2rem;
`;

const getItemStyle = (isDragging: any, draggableStyle: any) => ({
  // some basic styles to make the items look a bit nicer
  userSelect: "none",
  padding: "0",
  marginBottom: ".25rem",

  // change background colour if dragging
  //background: isDragging ? "lightgreen" : "grey",

  // styles we need to apply on draggables
  ...draggableStyle,
});

const getListStyle = (isDraggingOver: any) => ({
  //background: isDraggingOver ? "lightblue" : "lightgrey",
  padding: "1rem 0 1rem 0",
});

interface ReservationListProps {
  lja: ILja;
}

const ReservationList = ({ lja }: ReservationListProps) => {
  const dispatch = useAppDispatch();

  const ljaId = lja.id;
  const reservations = useAppSelector(selectReservationsByLjaId)(ljaId);
  const notes = useAppSelector(selectNotesByLjaId)(ljaId);
  const roleEnabled = useAppSelector(selectRoleEnabled)(lja.role);
  const canRelinquish = useAppSelector(selectCanRelinquishByRole)(lja.role);

  const orderedReservations = reservations.sort((a, b) =>
    a.order > b.order ? 1 : -1
  );

  const prettyRole = (role: string) => {
    if (role === "brsl_senior") return "Senior";
    else if (role === "brsl_junior") return "Junior";
    else if (role === "brsl_sophomore") return "Sophomore";
    else if (role === "brsl_freshman") return "Freshman";
    else return "unknown_role";
  };

  const renderStuff = () => {
    if (!roleEnabled) {
      return (
        <DisabledBackground>
          <DisabledRoleNotification />
        </DisabledBackground>
      );
    } else if (!lja.acknowledgesRelinquish && canRelinquish) {
      return (
        <DisabledBackground>
          <RelinquishSeats lja={lja} />
        </DisabledBackground>
      );
    } else if (lja.didRelinquish && canRelinquish) {
      return (
        <DisabledBackground primary>
          <RelinquishedNotification />
        </DisabledBackground>
      );
    } else if (!lja.acknowledgesResponsibility && roleEnabled) {
      return (
        <DisabledBackground>
          <AcknowledgeResponsibility lja={lja} />
        </DisabledBackground>
      );
    }
  };

  return (
    <Wrapper>
      <div style={{ padding: "3rem 1rem" }}>
        <p style={{ fontSize: "1.9rem" }}>
          Each <strong>{prettyRole(lja.role)}</strong> has{" "}
          {useAppSelector(selectSeatCountByRole)(lja.role)} prepaid seats. You
          can purchase additional seats below. Complete your reservations here
          and do not forget to click Save Changes when you are done.
        </p>
        {lja.role === "brsl_senior" && (
          <p style={{ fontSize: "1.9rem" }}>
            Each LJA Deb gets 1 Head Table facing the runway and all your other
            tables will fall in behind your Head Table. Guest 1-10 will be at
            your Head Table, guests 11-20 will be your 2nd table. If you
            purchase more seats the next guests will be at your third table and
            so on.
          </p>
        )}
        <Droppable droppableId={`droppable-${ljaId}`}>
          {(provided, snapshot) => (
            <div
              {...provided.droppableProps}
              ref={provided.innerRef}
              style={getListStyle(snapshot.isDraggingOver)}
            >
              {orderedReservations &&
                orderedReservations.map((reservation, idx) => (
                  <Draggable
                    key={reservation.id.toString()}
                    draggableId={reservation.id.toString()}
                    index={idx}
                  >
                    {(provided, snapshot) => (
                      <div
                        ref={provided.innerRef}
                        {...provided.draggableProps}
                        {...provided.dragHandleProps}
                        style={getItemStyle(
                          snapshot.isDragging,
                          provided.draggableProps.style
                        )}
                      >
                        <ReservationItem reservation={reservation} lja={lja} />
                      </div>
                    )}
                  </Draggable>
                ))}
              {provided.placeholder}
              <Heading>Extra Notes</Heading>
              <textarea
                placeholder="Please provide any seating preferences or other notes you have for this LJA"
                value={notes}
                onChange={(e) =>
                  dispatch(updateNote({ ljaId: ljaId, value: e.target.value }))
                }
              />
            </div>
          )}
        </Droppable>
      </div>
      {renderStuff()}
    </Wrapper>
  );
};

export default ReservationList;
