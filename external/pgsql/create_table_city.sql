CREATE TABLE city
(
  id serial NOT NULL,
  name character varying(100) NOT NULL,
  has_rental boolean NOT NULL DEFAULT false,
  CONSTRAINT city_pkey PRIMARY KEY (id)
)
WITH (
OIDS=FALSE
);