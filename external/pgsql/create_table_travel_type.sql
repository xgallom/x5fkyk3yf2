CREATE TABLE travel_type
(
  id serial NOT NULL,
  name character varying(16),
  is_provider boolean NOT NULL DEFAULT false,
  CONSTRAINT travel_type_pkey PRIMARY KEY (id)
)
WITH (
OIDS=FALSE
);

INSERT INTO travel_type VALUES (1, 'passenger', false);
INSERT INTO travel_type VALUES (2, 'car_company', true);
INSERT INTO travel_type VALUES (3, 'car_rental', true);
INSERT INTO travel_type VALUES (4, 'car_person', true);
INSERT INTO travel_type VALUES (5, 'other', false);
